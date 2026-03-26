<?php

declare(strict_types=1);

namespace App\Dto\CartItem;

final readonly class CartItemFiltersDto
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
        public string $sortBy = 'id',
        public string $sortDirection = 'asc',
    ) {}
}
