<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Clickhouse\ClickhouseManagerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Throwable;

final class SyncClickhouseActivitiesCommand extends Command
{
    protected $signature = 'clickhouse:sync-activities {--max-batches= : Stop after N batches (default: drain until empty)}';

    protected $description = 'Sync buffered activities from Redis Stream to ClickHouse';

    private const STREAM = 'clickhouse_activities_stream';

    private const GROUP = 'ch-sync-group';

    private const DLQ_STREAM = 'clickhouse_activities_dlq';

    private const BATCH_SIZE = 1000;

    private const IDLE_THRESHOLD_MS = 5 * 60 * 1000;

    public function handle(ClickhouseManagerService $clickhouseManagerService): int
    {
        if ($this->isPredis()) {
            $this->error('Stream sync requires phpredis. predis is not supported.');

            return self::FAILURE;
        }

        try {
            $this->ensureGroupExists();
        } catch (Throwable $e) {
            $this->error('Failed to ensure consumer group: '.$e->getMessage());

            return self::FAILURE;
        }

        $consumerName = 'consumer-'.Str::limit((string) gethostname(), 20).'-'.(string) getmypid();
        $maxBatches = $this->option('max-batches') !== null ? (int) $this->option('max-batches') : null;
        $batches = 0;
        $totalSynced = 0;

        try {
            do {
                $entries = $this->claimStaleEntries($consumerName);

                if (count($entries) < self::BATCH_SIZE) {
                    try {
                        $remaining = self::BATCH_SIZE - count($entries);
                        $fresh = Redis::xreadgroup(
                            self::GROUP,
                            $consumerName,
                            [self::STREAM => '>'],
                            $remaining,
                        );
                    } catch (Throwable $e) {
                        $this->error('xreadgroup failed: '.$e->getMessage());

                        return self::FAILURE;
                    }

                    if (is_array($fresh)) {
                        foreach ($fresh as $streamEntries) {
                            if (! is_array($streamEntries)) {
                                continue;
                            }

                            foreach ($streamEntries as $id => $fields) {
                                $entries[strval($id)] = $this->toStringKeyed($fields);
                            }
                        }
                    }
                }

                if ($entries === []) {
                    break;
                }

                [$rows, $goodIds, $badIds] = $this->decodeEntries($entries);

                $this->drainBadEntries($entries, $badIds);

                if ($rows === []) {
                    continue;
                }

                try {
                    $clickhouseManagerService->insertBatch('activity_log', $rows);
                } catch (Throwable $e) {
                    $this->error('ClickHouse insert failed: '.$e->getMessage());

                    return self::FAILURE;
                }

                try {
                    Redis::xack(self::STREAM, self::GROUP, $goodIds);
                    Redis::xdel(self::STREAM, $goodIds);
                } catch (Throwable $e) {
                    $this->warn('Failed to ack/del synced entries: '.$e->getMessage());
                }

                $totalSynced += count($rows);
                $batches++;
            } while ($maxBatches === null || $batches < $maxBatches);
        } finally {
            $this->trimStream();
            $this->deleteConsumer($consumerName);
        }

        if ($totalSynced > 0) {
            $this->info('Synced '.$totalSynced.' activities to ClickHouse in '.$batches.' batch(es).');
        }

        return self::SUCCESS;
    }

    private function ensureGroupExists(): void
    {
        try {
            Redis::xgroup('CREATE', self::STREAM, self::GROUP, '0', true);
        } catch (Throwable $e) {
            if (! str_contains($e->getMessage(), 'BUSYGROUP')) {
                throw $e;
            }
        }
    }

    private function deleteConsumer(string $consumerName): void
    {
        try {
            Redis::xgroup('DELCONSUMER', self::STREAM, self::GROUP, $consumerName);
        } catch (Throwable) {
            // best-effort
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function claimStaleEntries(string $consumerName): array
    {
        $result = [];
        $cursor = '0-0';

        do {
            try {
                $claim = Redis::xautoclaim(
                    self::STREAM,
                    self::GROUP,
                    $consumerName,
                    self::IDLE_THRESHOLD_MS,
                    $cursor,
                    100,
                );
            } catch (Throwable $e) {
                $this->error('xautoclaim failed: '.$e->getMessage());

                break;
            }

            if (! is_array($claim)) {
                break;
            }

            $claimed = $claim[1] ?? [];
            if (! is_array($claimed)) {
                $claimed = [];
            }

            foreach ($claimed as $id => $fields) {
                if (count($result) >= self::BATCH_SIZE) {
                    break 2;
                }

                $id = (string) $id;
                $result[$id] = $this->toStringKeyed($fields);
            }

            $newCursor = $claim[0] ?? '0-0';
            if (! is_string($newCursor) || $newCursor === '0-0') {
                break;
            }
            $cursor = $newCursor;
        } while (true);

        if ($result !== []) {
            $this->warn('Reclaimed '.count($result).' stale entries from previous run.');
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $entries
     * @return array{0: list<array<string, mixed>>, 1: list<string>, 2: array<string, string>}
     */
    private function decodeEntries(array $entries): array
    {
        $rows = [];
        $goodIds = [];
        $badIds = [];

        foreach ($entries as $id => $fields) {
            try {
                $fields = $this->toStringKeyed($fields);
                $payload = $fields['payload'] ?? '';
                $json = is_string($payload) ? $payload : '';

                if ($json === '') {
                    $badIds[$id] = 'decode_error: empty payload';

                    continue;
                }

                $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

                if (! is_array($decoded)) {
                    $badIds[$id] = 'decode_error: payload is not an object';

                    continue;
                }

                $row = $this->toStringKeyed($decoded);

                if (! isset($row['id'])) {
                    $badIds[$id] = 'decode_error: missing required field "id"';

                    continue;
                }

                $rows[] = $row;
                $goodIds[] = $id;
            } catch (Throwable $e) {
                $badIds[$id] = 'decode_error: '.$e->getMessage();
            }
        }

        return [$rows, $goodIds, $badIds];
    }

    /**
     * @param  array<string, mixed>  $entries
     * @param  array<string, string>  $badIds
     */
    private function drainBadEntries(array $entries, array $badIds): void
    {
        if ($badIds === []) {
            return;
        }

        foreach ($badIds as $id => $reason) {
            $payload = $this->toStringKeyed($entries[$id] ?? [])['payload'] ?? null;
            $this->moveToDlq($id, is_string($payload) ? $payload : null, $reason);
        }

        try {
            Redis::xack(self::STREAM, self::GROUP, array_keys($badIds));
            Redis::xdel(self::STREAM, array_keys($badIds));
        } catch (Throwable $e) {
            $this->warn('Failed to ack/del bad entries: '.$e->getMessage());
        }
    }

    private function moveToDlq(string $id, ?string $payload, string $reason): void
    {
        try {
            Redis::xadd(self::DLQ_STREAM, '*', [
                'original_id' => $id,
                'payload' => $payload ?? '',
                'reason' => $reason,
                'failed_at' => now()->toIso8601String(),
            ], $this->getStreamMaxLen(), false);
        } catch (Throwable $e) {
            $this->warn('Failed to move entry '.$id.' to DLQ: '.$e->getMessage());
        }
    }

    private function trimStream(): void
    {
        try {
            Redis::xtrim(self::STREAM, (string) $this->getStreamMaxLen(), false);
        } catch (Throwable) {
            // best-effort, non-critical
        }
    }

    private function getStreamMaxLen(): int
    {
        $value = config('clickhouse.stream_max_len', 100_000);

        return is_numeric($value) ? (int) $value : 100_000;
    }

    private function isPredis(): bool
    {
        $value = config('database.redis.client');

        return $value === 'predis';
    }

    /**
     * @return array<string, mixed>
     */
    private function toStringKeyed(mixed $fields): array
    {
        if (! is_array($fields)) {
            return [];
        }

        return array_combine(
            array_map(strval(...), array_keys($fields)),
            array_values($fields),
        );
    }
}
