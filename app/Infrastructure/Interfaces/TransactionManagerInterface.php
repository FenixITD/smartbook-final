<?php

declare(strict_types=1);

namespace App\Infrastructure\Interfaces;

interface TransactionManagerInterface
{
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
    public function transaction(callable $callback): mixed;
}
