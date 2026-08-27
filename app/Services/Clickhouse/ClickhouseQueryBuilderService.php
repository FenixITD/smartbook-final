<?php

declare(strict_types=1);

namespace App\Services\Clickhouse;

use function count;
use function is_int;

class ClickhouseQueryBuilderService
{
    /** @var array<int, string> */
    private array $wheres = [];

    /** @var array<string, mixed> */
    private array $bindings = [];

    private int|null $limitValue = null;

    private int|null $offsetValue = null;

    private string $orderByColumn = 'created_at';

    private string $orderByDirection = 'DESC';

    private bool $useFinal = false;

    public function __construct(
        private readonly ClickhouseManagerService $manager,
        private readonly string $table,
    ) {
    }

    public function where(string $column, mixed $value): static
    {
        $key = $column.'_'.count($this->bindings);
        $type = is_int($value) ? 'UInt64' : 'String';
        $this->wheres[] = "{$column} = {{$key}:{$type}}";
        $this->bindings[$key] = $value;

        return $this;
    }

    /**
     * @param array<int, mixed> $values
     */
    public function whereIn(string $column, array $values): static
    {
        $keys = [];

        foreach ($values as $value) {
            $key = $column.'_'.count($this->bindings);
            $type = is_int($value) ? 'UInt64' : 'String';
            $keys[] = "{{$key}:{$type}}";
            $this->bindings[$key] = $value;
        }

        $placeholders = implode(',', $keys);
        $this->wheres[] = "{$column} IN ({$placeholders})";

        return $this;
    }

    public function orderByDesc(string $column): static
    {
        $this->orderByColumn = $column;
        $this->orderByDirection = 'DESC';

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limitValue = $limit;

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offsetValue = $offset;

        return $this;
    }

    public function final(): static
    {
        $this->useFinal = true;

        return $this;
    }

    public function count(): int
    {
        $where = $this->buildWhere();
        $final = $this->useFinal ? ' FINAL' : '';

        return $this->manager->count(
            "SELECT count() as count FROM {$this->table}{$final}{$where}",
            $this->bindings,
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function get(): array
    {
        $where = $this->buildWhere();
        $final = $this->useFinal ? ' FINAL' : '';
        $order = " ORDER BY {$this->orderByColumn} {$this->orderByDirection}";
        $limit = $this->limitValue !== null ? " LIMIT {$this->limitValue}" : '';
        $offset = $this->offsetValue !== null ? " OFFSET {$this->offsetValue}" : '';

        return $this->manager->select(
            "SELECT * FROM {$this->table}{$final}{$where}{$order}{$limit}{$offset}",
            $this->bindings,
        );
    }

    private function buildWhere(): string
    {
        if ($this->wheres === []) {
            return '';
        }

        return ' WHERE '.implode(' AND ', $this->wheres);
    }
}
