<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ClickhouseActivity;
use App\Services\Clickhouse\ClickhouseActivityService;
use App\Services\Clickhouse\ClickhouseManagerService;

final readonly class ClickhouseActivityObserver
{
    public function __construct(
        private ClickhouseActivityService $service,
        private ClickhouseManagerService $clickhouseManagerService,
    ) {
    }

    public function saving(ClickhouseActivity $activity): bool
    {
        $row = $this->service->buildRow($activity);

        $this->clickhouseManagerService->insert('activity_log', $row);

        /** @var int $id */
        $id = $row['id'];

        $activity->id = $id;
        $activity->exists = true;
        $activity->wasRecentlyCreated = true;

        return false;
    }
}
