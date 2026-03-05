<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\DTO\CartItem\CartItemFiltersDTO;
use App\DTO\CartItem\CartItemResponseDTO;
use App\Models\CartItem;

interface CartItemRepositoryInterface
{
    /**
     * @return array<CartItemResponseDTO>
     */
    public function getList(CartItemFiltersDTO $filters): array;

    public function getById(int $id): ?CartItemResponseDTO;

    public function create(array $data): CartItemResponseDTO;

    public function update(CartItem $cartItem, array $data): ?CartItemResponseDTO;

    public function delete(CartItem $cartItem): bool;
}
