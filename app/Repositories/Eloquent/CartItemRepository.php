<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTO\CartItem\CartItemFiltersDto;
use App\DTO\CartItem\CartItemResponseDto;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;

final class CartItemRepository implements CartItemRepositoryInterface
{
    public function getList(CartItemFiltersDto $filters): array
    {
        $query = CartItem::query()
            ->when($filters->search !== null, fn ($q) => $q->where('id', 'like', "%{$filters->search}%"));

        $paginator = $query->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return $paginator->getCollection()
            ->map(fn (CartItem $cartItem) => CartItemResponseDto::fromModel($cartItem))->all();
    }

    public function getById(int $id): ?CartItemResponseDto
    {
        $cartItem = CartItem::find($id);

        return $cartItem ? CartItemResponseDto::fromModel($cartItem) : null;
    }

    public function create(array $data): CartItemResponseDto
    {
        $cartItem = CartItem::create($data);

        return CartItemResponseDto::fromModel($cartItem);
    }

    public function update(CartItem $cartItem, array $data): ?CartItemResponseDto
    {
        $cartItem->update($data);

        return CartItemResponseDto::fromModel($cartItem->fresh());
    }

    public function delete(CartItem $cartItem): bool
    {
        return $cartItem->delete();
    }
}
