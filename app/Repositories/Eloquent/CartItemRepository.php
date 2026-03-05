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
            ->join('books', 'cart_items.book_id', '=', 'books.id')
            ->select('cart_items.*')
            ->where('cart_items.user_id', auth()->id()) // only the current user's cart
            ->when($filters->search !== null, function ($q) use ($filters) {
                $q->where('books.title', 'like', "%{$filters->search}%");
            });

        $paginator = $query->orderBy(
            $filters->sortBy === 'title' ? 'books.title' : 'cart_items.'.$filters->sortBy,
            $filters->sortDirection
        )->paginate($filters->perPage);

        return CartItemResponseDTO::fromCollection($paginator->getCollection());
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
