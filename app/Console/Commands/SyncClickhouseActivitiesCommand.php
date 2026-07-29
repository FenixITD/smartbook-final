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

        $consumerName = 'consumer-' . gethostname() . '-' . getmypid();

        $entries = $this->claimStaleEntries($consumerName);

        if (count($entries) < self::BATCH_SIZE) {
            $fresh = Redis::xReadGroup(
                self::GROUP,
                $consumerName,
                [self::STREAM => '>'],
                self::BATCH_SIZE - count($entries)
            );
            foreach ($fresh[self::STREAM] ?? [] as $id => $fields) {
                $entries[$id] = $fields;
            }
        }

        if ($entries === []) {
            return self::SUCCESS;
        }

        [$rows, $goodIds, $badIds] = $this->decodeEntries($entries);

        foreach ($badIds as $id => $reason) {
            $this->moveToDlq($id, $entries[$id]['payload'] ?? null, $reason);
        }
        if ($badIds !== []) {
            Redis::xAck(self::STREAM, self::GROUP, array_keys($badIds));
            Redis::xDel(self::STREAM, array_keys($badIds));
        }

        if ($rows === []) {
            return self::SUCCESS;
        }

        try {
            $clickhouseManagerService->insertBatch('activity_log', $rows);
            Redis::xAck(self::STREAM, self::GROUP, $goodIds);
            Redis::xDel(self::STREAM, $goodIds);
            $this->info('Synced ' . count($rows) . ' activities to ClickHouse.');
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $this->warn(count($rows) . ' activities will be retried on next run.');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function ensureGroupExists(): void
    {
        try {
            Redis::xGroup('CREATE', self::STREAM, self::GROUP, '0', true);
        } catch (Throwable $e) {
            if (!str_contains($e->getMessage(), 'BUSYGROUP')) {
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
            $claim = Redis::xAutoClaim(
                self::STREAM,
                self::GROUP,
                $consumerName,
                self::IDLE_THRESHOLD_MS,
                $cursor,
                100
            );
            $cursor = $claim[0];
            $claimed = $claim[1] ?? [];

            foreach ($claimed as $id => $fields) {
                $deliveryCount = $this->deliveryCount($id);

                if ($deliveryCount > self::MAX_DELIVERIES) {
                    $this->moveToDlq($id, $fields['payload'] ?? null, 'max_deliveries_exceeded');
                    Redis::xAck(self::STREAM, self::GROUP, [$id]);
                    Redis::xDel(self::STREAM, [$id]);
                    $this->warn("Entry {$id} moved to dead letter after " . self::MAX_DELIVERIES . ' attempts.');
                    continue;
                }

                $result[$id] = $fields;
            }
        } while ($cursor !== '0-0');

        if ($result !== []) {
            $this->warn('Reclaimed ' . count($result) . ' stale entries from previous run.');
        }

        return $result;
    }

    private function deliveryCount(string $id): int
    {
        $pending = Redis::xPending(self::STREAM, self::GROUP, $id, $id, 1);
        return $pending[0][3] ?? 1;
    }

    /**
     * @param array<string, array<string, mixed>> $entries
     * @return array{0: list<array<string, mixed>>, 1: list<string>, 2: array<string, string>}
     */
    private function decodeEntries(array $entries): array
    {
        $rows = [];
        $goodIds = [];
        $badIds = [];

        foreach ($entries as $id => $fields) {
            try {
                $rows[] = json_decode((string) ($fields['payload'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
                $goodIds[] = $id;
            } catch (Throwable $e) {
                $badIds[$id] = 'decode_error: ' . $e->getMessage();
            }
        }

        return [$rows, $goodIds, $badIds];
    }

    private function moveToDlq(string $id, ?string $payload, string $reason): void
    {
        Redis::xAdd(self::DLQ_STREAM, '*', [
            'original_id' => $id,
            'payload' => $payload ?? '',
            'reason' => $reason,
            'failed_at' => now()->toIso8601String(),
        ]);
    }
}
