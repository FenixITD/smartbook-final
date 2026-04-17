<?php

declare(strict_types=1);

namespace App\Dto\Order;

final readonly class OrderFiltersDto
{
    public function __construct(
        public string|null $search = null,
        public int $perPage = 15,
        public string $sortBy = 'id',
        public string $sortDirection = 'asc',
    ) {
    }
}
