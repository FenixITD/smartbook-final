<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemFiltersDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Models\CartItem;

interface CartItemRepositoryInterface
{
    /**
     * @return array<CartItemResponseDto>
     */
    public function getList(CartItemFiltersDto $filters): array;

    public function getById(int $id): ?CartItemResponseDto;

    public function addOrIncrement(CartItemDto $data): void;

    public function updateQuantity(CartItem $cartItem, int $quantity): void;

    public function create(CartItemDto $data): CartItemResponseDto;

    public function update(CartItem $cartItem, CartItemDto $data): ?CartItemResponseDto;

    public function delete(CartItem $cartItem): bool;
}
