<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Clickhouse\ClickhouseManagerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
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

    public function handle(ClickhouseManagerService $clickhouseManagerService): int
    {
        $this->ensureGroupExists();

        $consumerName = 'consumer-'.gethostname().'-'.getmypid();

        $entries = $this->claimStaleEntries($consumerName);

        if (count($entries) < self::BATCH_SIZE) {
            $fresh = Redis::xreadgroup(
                self::GROUP,
                $consumerName,
                [self::STREAM => '>'],
                self::BATCH_SIZE - count($entries)
            );

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

        foreach ($badIds as $id => $reason) {
            $payload = $this->toStringKeyed($entries[$id])['payload'] ?? null;
            $this->moveToDlq($id, is_string($payload) ? $payload : null, $reason);
        }
        if ($badIds !== []) {
            Redis::xack(self::STREAM, self::GROUP, array_keys($badIds));
            Redis::xdel(self::STREAM, array_keys($badIds));
        }

        if ($rows === []) {
            return self::SUCCESS;
        }

        try {
            $clickhouseManagerService->insertBatch('activity_log', $rows);
            Redis::xack(self::STREAM, self::GROUP, $goodIds);
            Redis::xdel(self::STREAM, $goodIds);
            $this->info('Synced '.count($rows).' activities to ClickHouse.');
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $this->warn(count($rows).' activities will be retried on next run.');

            return self::FAILURE;
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

    /**
     * @return array<string, mixed>
     */
    private function claimStaleEntries(string $consumerName): array
    {
        $result = [];
        $cursor = '0-0';

        do {
            $claim = Redis::xautoclaim(
                self::STREAM,
                self::GROUP,
                $consumerName,
                self::IDLE_THRESHOLD_MS,
                $cursor,
                100
            );

            if (! is_array($claim)) {
                break;
            }

            $claimed = $claim[1] ?? [];
            if (! is_array($claimed)) {
                $claimed = [];
            }

            foreach ($claimed as $id => $fields) {
                if (! is_string($id)) {
                    $id = strval($id);
                }
                $deliveryCount = $this->deliveryCount($id);

                if ($deliveryCount > self::MAX_DELIVERIES) {
                    $payload = $this->toStringKeyed($fields)['payload'] ?? null;
                    $this->moveToDlq($id, is_string($payload) ? $payload : null, 'max_deliveries_exceeded');
                    Redis::xack(self::STREAM, self::GROUP, [$id]);
                    Redis::xdel(self::STREAM, [$id]);
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
        $pending = Redis::xpending(self::STREAM, self::GROUP, $id, $id, 1);
        $pending = is_array($pending) ? $pending : [];
        $first = $pending[0] ?? null;
        $count = is_array($first) ? ($first[3] ?? 1) : 1;

        return is_int($count) ? $count : 1;
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
                $decoded = json_decode(is_string($payload) ? $payload : '', true, 512, JSON_THROW_ON_ERROR);
                $rows[] = $this->toStringKeyed($decoded);
                $goodIds[] = $id;
            } catch (Throwable $e) {
                $badIds[$id] = 'decode_error: '.$e->getMessage();
            }
        }

        return [$rows, $goodIds, $badIds];
    }

    private function moveToDlq(string $id, ?string $payload, string $reason): void
    {
        Redis::xadd(self::DLQ_STREAM, '*', [
            'original_id' => $id,
            'payload' => $payload ?? '',
            'reason' => $reason,
            'failed_at' => now()->toIso8601String(),
        ]);
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
            array_values($fields)
        );
    }
}
