<?php

declare(strict_types=1);

namespace App\DTO\CartItem;

use App\Http\Requests\CartItemRequest;

final readonly class CartItemFiltersDTO
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
        public string $sortBy = 'created_at',
        public string $sortDirection = 'desc',
    ) {}

    public static function fromRequest(CartItemRequest $request): self
    {
        return new self(
            search: $request->string('search')->nullable(),
            perPage: $request->integer('per_page', 15),
            sortBy: (string) $request->string('sort_by', 'created_at'),
            sortDirection: (string) $request->string('sort_direction', 'desc'),
        );
    }
}
