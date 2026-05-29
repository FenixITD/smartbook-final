<?php

declare(strict_types=1);

namespace App\Services\Clickhouse;

use ClickHouseDB\Client;

final class ClickhouseManager
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
        $this->client->setTimeout(config('clickhouse.timeout'));
        $this->client->setConnectTimeOut(config('clickhouse.connect_timeout'));
    }

    /**
     * Insert a single row. Keys are used as column names.
     *
     * @param array<string, mixed> $row
     */
    public function insert(string $table, array $row): void
    {
        $columns = array_keys($row);
        $values = array_values($row);

        $this->client->insert($table, [$values], $columns);
    }

    /**
     * Run a SELECT and return all rows.
     *
     * @param array<string, mixed> $bindings Named bindings, referenced as {name:Type} in SQL
     * @return array<array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array
    {
        return $this->client->select($sql, $bindings)->rows();
    }

    /**
     * Run a COUNT query and return the integer result.
     *
     * @param array<string, mixed> $bindings
     */
    public function count(string $sql, array $bindings = []): int
    {
        $rows = $this->client->select($sql, $bindings)->rows();

        return (int) ($rows[0]['count'] ?? 0);
    }

    /**
     * Execute a DDL statement (CREATE TABLE, CREATE DATABASE, etc.)
     */
    public function execute(string $sql): void
    {
        $this->client->write($sql);
    }

    public function ping(): bool
    {
        return $this->client->ping();
    }
}
