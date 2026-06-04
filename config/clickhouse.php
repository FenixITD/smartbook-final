<?php

declare(strict_types=1);

return [
    'host' => env('CLICKHOUSE_HOST', 'clickhouse'),
    'port' => (int) env('CLICKHOUSE_PORT', 8123),
    'database' => env('CLICKHOUSE_DATABASE', 'smartbook'),
    'username' => env('CLICKHOUSE_USERNAME', 'default'),
    'password' => env('CLICKHOUSE_PASSWORD', ''),
    'timeout' => (float) env('CLICKHOUSE_TIMEOUT', 2.0),
    'connect_timeout' => (float) env('CLICKHOUSE_CONNECT_TIMEOUT', 2.0),
    'migrations_path' => database_path('clickhouse'),
];
