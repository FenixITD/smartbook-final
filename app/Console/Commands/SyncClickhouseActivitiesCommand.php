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

    protected $description = 'Sync buffered activities from Redis to ClickHouse';

    public function handle(ClickhouseManagerService $clickhouseManagerService): int
    {
        $batchSize = 1000;
        $rows = [];

        while (true) {
            $item = Redis::lPop('clickhouse_activities_buffer');

            if ($item === false) {
                break;
            }

            /** @var array<string, mixed> $decoded */
            $decoded = json_decode((string) $item, true, 512, JSON_THROW_ON_ERROR);
            $rows[] = $decoded;

            if (count($rows) >= $batchSize) {
                break;
            }
        }

        if ($rows !== []) {
            try {
                $clickhouseManagerService->insertBatch('activity_log', $rows);
            } catch (Throwable $e) {
                foreach ($rows as $row) {
                    Redis::rPush('clickhouse_activities_buffer', json_encode($row));
                }

                $this->error($e->getMessage());

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
