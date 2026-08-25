<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class RetryDlqCommand extends Command
{
    protected $signature = 'clickhouse:retry-dlq {--limit=500 : Max entries to retry per run}';

    protected $description = 'Replay DLQ entries back into the main activity stream for retry';

    private const DLQ_STREAM = 'clickhouse_activities_dlq';

    private const TARGET_STREAM = 'clickhouse_activities_stream';

    private const BATCH_SIZE = 100;

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $retried = 0;
        $cursor = '0-0';

        do {
            $batch = min($limit - $retried, self::BATCH_SIZE);

            try {
                $result = Redis::xread(
                    [self::DLQ_STREAM => $cursor],
                    $batch,
                );
            } catch (Throwable $e) {
                $this->error('xread from DLQ failed: '.$e->getMessage());

                return self::FAILURE;
            }

            if (! is_array($result) || $result === []) {
                break;
            }

            $entries = $result[self::DLQ_STREAM] ?? [];

            if (! is_array($entries) || $entries === []) {
                break;
            }

            $lastId = '0-0';

            foreach ($entries as $id => $fields) {
                $lastId = (string) $id;

                if ($retried >= $limit) {
                    break;
                }

                $payload = $this->toStringKeyed($fields)['payload'] ?? null;

                if (! is_string($payload) || $payload === '') {
                    $this->warn('Skipping DLQ entry '.$id.': empty payload.');

                    $this->deleteFromDlq($id);

                    continue;
                }

                try {
                    Redis::xadd(self::TARGET_STREAM, '*', ['payload' => $payload]);
                    $this->deleteFromDlq($id);
                    $retried++;
                } catch (Throwable $e) {
                    $this->warn('Failed to replay DLQ entry '.$id.': '.$e->getMessage());
                }
            }

            $cursor = $lastId;

        } while (true);

        $this->info('Replayed '.$retried.' entries from DLQ to main stream.');

        return self::SUCCESS;
    }

    private function deleteFromDlq(string $id): void
    {
        try {
            Redis::xdel(self::DLQ_STREAM, [$id]);
        } catch (Throwable $e) {
            $this->warn('Failed to delete DLQ entry '.$id.': '.$e->getMessage());
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
