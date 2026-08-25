<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ClickhouseActivity;
use App\Services\Clickhouse\ClickhouseActivityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

final readonly class ClickhouseActivityObserver
{
    public function __construct(
        private ClickhouseActivityService $service,
    ) {}

    public function saving(ClickhouseActivity $activity): bool
    {
        $row = $this->service->buildRow($activity);

        DB::afterCommit(function () use ($row): void {
            try {
                $payload = json_encode($row, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                Redis::xadd('clickhouse_activities_stream', '*', ['payload' => $payload], ['MAXLEN', config('clickhouse.stream_max_len', 100_000)]);
            } catch (Throwable $e) {
                Log::warning('Failed to push activity to Redis stream', [
                    'error' => $e->getMessage(),
                    'row_id' => $row['id'] ?? null,
                ]);
            }
        });

        $activity->id = is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0;
        $activity->exists = true;
        $activity->wasRecentlyCreated = true;

        return false;
    }
}
