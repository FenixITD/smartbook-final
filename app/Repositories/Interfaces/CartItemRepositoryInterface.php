<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\DTO\CartItem\CartItemFiltersDto;
use App\DTO\CartItem\CartItemResponseDto;
use App\Models\CartItem;

interface CartItemRepositoryInterface
{
    /**
     * @return array<CartItemResponseDto>
     */
    public function getList(CartItemFiltersDto $filters): array;

    public function getById(int $id): ?CartItemResponseDto;

    public function create(array $data): CartItemResponseDto;

    public function update(CartItem $cartItem, array $data): ?CartItemResponseDto;

    public function delete(CartItem $cartItem): bool;
}
