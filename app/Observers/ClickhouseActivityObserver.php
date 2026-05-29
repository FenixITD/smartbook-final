<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ClickhouseActivity;
use App\Services\Clickhouse\ClickhouseActivityService;
use App\Services\Clickhouse\ClickhouseManager;

final readonly class ClickhouseActivityObserver
{
    public function __construct(
        private ClickhouseActivityService $service,
        private ClickhouseManager $ch,
    ) {}

    public function saving(ClickhouseActivity $activity): bool
    {
        $row = $this->service->buildRow($activity);

        $this->ch->insert('activity_log', $row);

        $activity->id = $row['id'];
        $activity->exists = true;
        $activity->wasRecentlyCreated = true;

        return false;
    }
}
