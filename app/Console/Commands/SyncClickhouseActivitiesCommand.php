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
    protected $signature = 'clickhouse:sync-activities';

    protected $description = 'Sync buffered activities from Redis Stream to ClickHouse';

    private const STREAM = 'clickhouse_activities_stream';

    private const GROUP = 'ch-sync-group';

    private const DLQ_STREAM = 'clickhouse_activities_dlq';

    private const BATCH_SIZE = 1000;

    private const MAX_DELIVERIES = 3;

    private const IDLE_THRESHOLD_MS = 5 * 60 * 1000;

    private const MAX_STREAM_LEN = 100_000;

    public function handle(ClickhouseManagerService $clickhouseManagerService): int
    {
        $this->ensureGroupExists();

        $consumerName = 'consumer-'.Str::limit(gethostname(), 20).'-'.getmypid();

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
            return self::SUCCESS;
        }

        [$rows, $goodIds, $badIds] = $this->decodeEntries($entries);

        $this->drainBadEntries($entries, $badIds);

        if ($rows === []) {
            return self::SUCCESS;
        }

        try {
            $clickhouseManagerService->insertBatch('activity_log', $rows);
        } catch (Throwable $e) {
            $this->error('ClickHouse insert failed: '.$e->getMessage());
            $this->drainBadEntries($entries, array_flip($goodIds));

            return self::FAILURE;
        }

        try {
            Redis::xack(self::STREAM, self::GROUP, $goodIds);
            Redis::xdel(self::STREAM, $goodIds);
        } catch (Throwable $e) {
            $this->warn('Failed to ack/del synced entries: '.$e->getMessage());
        }

        $this->info('Synced '.count($rows).' activities to ClickHouse.');

        $this->trimStream();

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
                $id = (string) $id;
                $deliveryCount = $this->deliveryCount($id);

                if ($deliveryCount > self::MAX_DELIVERIES) {
                    $payload = $this->toStringKeyed($fields)['payload'] ?? null;
                    $this->moveToDlq($id, is_string($payload) ? $payload : null, 'max_deliveries_exceeded');
                    try {
                        Redis::xack(self::STREAM, self::GROUP, [$id]);
                        Redis::xdel(self::STREAM, [$id]);
                    } catch (Throwable $e) {
                        $this->warn('Failed to ack/del stale entry '.$id.': '.$e->getMessage());
                    }
                    $this->warn('Entry '.$id.' moved to dead letter after '.self::MAX_DELIVERIES.' attempts.');

                    continue;
                }

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

    private function deliveryCount(string $id): int
    {
        try {
            $pending = Redis::xpending(self::STREAM, self::GROUP, $id, $id, 1);

            if (! is_array($pending) || $pending === []) {
                return 1;
            }

            $first = $pending[0] ?? null;

            if (! is_array($first)) {
                return 1;
            }

            $count = $first[3] ?? null;

            return is_int($count) ? $count : 1;
        } catch (Throwable) {
            return 1;
        }
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

                if ($row === []) {
                    $badIds[$id] = 'decode_error: decoded payload is empty';

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
            ], ['MAXLEN', self::MAX_STREAM_LEN]);
        } catch (Throwable $e) {
            $this->warn('Failed to move entry '.$id.' to DLQ: '.$e->getMessage());
        }
    }

    private function trimStream(): void
    {
        try {
            Redis::xtrim(self::STREAM, 'MAXLEN', self::MAX_STREAM_LEN);
        } catch (Throwable) {
            // best-effort, non-critical
        }
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
