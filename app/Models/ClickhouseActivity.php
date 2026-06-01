<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Clickhouse\ClickhouseActivityService;
use App\Services\Clickhouse\ClickhouseManagerService;
use Spatie\Activitylog\Models\Activity;

final class ClickhouseActivity extends Activity
{
    public function save(array $options = []): bool
    {
        $service = new ClickhouseActivityService();
        $manager = new ClickhouseManagerService();

        $row = $service->buildRow($this);
        $manager->insert('activity_log', $row);

        return true;
    }
}
