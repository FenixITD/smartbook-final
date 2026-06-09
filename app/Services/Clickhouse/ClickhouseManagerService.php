<?php

declare(strict_types=1);

namespace App\Services\Clickhouse;

use ClickHouseDB\Client;

class ClickhouseManagerService
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

        $db = config('clickhouse.database');
        $this->client->database(is_scalar($db) ? (string) $db : '');

        $timeout = config('clickhouse.timeout');
        $this->client->setTimeout(is_numeric($timeout) ? (int) $timeout : 1);

        $connectTimeout = config('clickhouse.connect_timeout');
        $this->client->setConnectTimeOut(is_numeric($connectTimeout) ? (float) $connectTimeout : 1.0);
    }

    /**
     * @param array<string, mixed> $row
     */
    public function insert(string $table, array $row): void
    {
        $columns = array_keys($row);
        $values = array_values($row);

        $this->client->insert($table, [$values], $columns);
    }

    /**
     * @param array<string, mixed> $bindings Named bindings, referenced as {name:Type} in SQL
     *
     * @return array<int, array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array
    {
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $this->client->select($sql, $bindings)->rows();

        return $rows;
    }

    /**
     * @param array<string, mixed> $bindings
     */
    public function count(string $sql, array $bindings = []): int
    {
        $rows = $this->select($sql, $bindings);

        if (isset($rows[0]['count']) && is_scalar($rows[0]['count'])) {
            return (int) $rows[0]['count'];
        }

        return 0;
    }

    public function execute(string $sql): void
    {
        $this->client->write($sql);
    }

    public function ping(): bool
    {
        return $this->client->ping();
    }

    public function table(string $table): ClickhouseQueryBuilderService
    {
        return new ClickhouseQueryBuilderService($this, $table);
    }
}
