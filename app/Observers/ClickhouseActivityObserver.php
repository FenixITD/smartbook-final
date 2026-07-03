<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ClickhouseActivity;
use App\Services\Clickhouse\ClickhouseActivityService;
use Illuminate\Support\Facades\Redis;

final readonly class ClickhouseActivityObserver
{
    public function __construct(
        private ClickhouseActivityService $service,
    ) {
    }

    public function saving(ClickhouseActivity $activity): bool
    {
        $row = $this->service->buildRow($activity);

        Redis::rpush('clickhouse_activities_buffer', json_encode($row));

        $activity->id = $row['id'];
        $activity->exists = true;
        $activity->wasRecentlyCreated = true;

        return false;
    }
}
