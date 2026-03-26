<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\CartItem\CartItemDto;
use App\DTO\CartItem\CartItemFiltersDto;
use App\DTO\CartItem\CartItemResponseDto;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;

final class CartItemRepository implements CartItemRepositoryInterface
{
    public function getList(CartItemFiltersDto $filters): array
    {
        return CartItem::query()
            ->when($filters->search !== null, fn ($q) => $q->where('id', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(fn (CartItem $cartItem) => CartItemResponseDto::fromModel($cartItem))
            ->all();
    }

    public function getById(int $id): ?CartItemResponseDto
    {
        $cartItem = CartItem::find($id);

        return $cartItem ? CartItemResponseDto::fromModel($cartItem) : null;
    }

    public function create(CartItemDto $data): CartItemResponseDto
    {
        $cartItem = CartItem::create($data->toArray());

        return CartItemResponseDto::fromModel($cartItem);
    }

    public function update(CartItem $cartItem, CartItemDto $data): ?CartItemResponseDto
    {
        $cartItem->update($data->toArray());

        return CartItemResponseDto::fromModel($cartItem->fresh());
    }

    public function delete(CartItem $cartItem): bool
    {
        return $cartItem->delete();
    }
}
