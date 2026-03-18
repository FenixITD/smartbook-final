<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTO\CartItem\CartItemFiltersDTO;
use App\DTO\CartItem\CartItemResponseDTO;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;

final class CartItemRepository implements CartItemRepositoryInterface
{
    public function getList(CartItemFiltersDTO $filters): array
    {
        $query = CartItem::query()
            ->when($filters->search !== null, fn ($q) => $q->where('id', 'like', "%{$filters->search}%"));

        $paginator = $query->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return $paginator->getCollection()
            ->map(fn (CartItem $cartItem) => CartItemResponseDTO::fromModel($cartItem))->all();
    }

    public function getById(int $id): ?CartItemResponseDTO
    {
        $cartItem = CartItem::find($id);

        return $cartItem ? CartItemResponseDTO::fromModel($cartItem) : null;
    }

    public function create(array $data): CartItemResponseDTO
    {
        $cartItem = CartItem::create($data);

        return CartItemResponseDTO::fromModel($cartItem);
    }

    public function update(CartItem $cartItem, array $data): ?CartItemResponseDTO
    {
        $cartItem->update($data);

        return CartItemResponseDTO::fromModel($cartItem->fresh());
    }

    public function delete(CartItem $cartItem): bool
    {
        return $cartItem->delete();
    }
}
