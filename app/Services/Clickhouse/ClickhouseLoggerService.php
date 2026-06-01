<?php

declare(strict_types=1);

namespace App\Services\Clickhouse;

use ClickHouseDB\Client;

class ClickhouseLoggerService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'host' => config('clickhouse.host'),
            'port' => config('clickhouse.port'),
            'username' => config('clickhouse.username'),
            'password' => config('clickhouse.password'),
        ]);
        $this->client->database(config('clickhouse.database'));
    }

    public function log(
        string $logName,
        string $description,
        string $subjectType = '',
        ?int $subjectId = null,
        string $causerType = '',
        ?int $causerId = null,
        string $causerName = '',
        array $properties = [],
    ): void {
        $id = (int) (microtime(true) * 1000) * 1000 + random_int(0, 999);

        $this->client->insert('activity_log', [[
            $id,
            $logName,
            $description,
            $subjectType,
            $subjectId,
            $causerType,
            $causerId,
            $causerName,
            json_encode($properties),
        ]], [
            'id', 'log_name', 'description', 'subject_type',
            'subject_id', 'causer_type', 'causer_id', 'causer_name', 'properties',
        ]);
    }
}
