<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\CartItem\CartItemDto;
use App\DTO\CartItem\CartItemFiltersDto;
use App\DTO\CartItem\CartItemResponseDto;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Collection;

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

    public function findByUserAndBook(int $userId, int $bookId): ?CartItem
    {
        return CartItem::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->first();
    }

    public function getByUserId(int $userId): Collection
    {
        return CartItem::with('book.author')
            ->where('user_id', $userId)
            ->get();
    }

    public function countByUserId(int $userId): int
    {
        return CartItem::where('user_id', $userId)->count();
    }

    public function deleteByUserId(int $userId): void
    {
        CartItem::where('user_id', $userId)->delete();
    }

    public function addOrIncrement(CartItemDto $data): void
    {
        $existing = CartItem::where('user_id', $data->userId)
            ->where('book_id', $data->bookId)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $data->quantity);
        } else {
            CartItem::create($data->toArray());
        }
    }

    public function updateQuantity(CartItem $cartItem, int $quantity): void
    {
        $cartItem->update(['quantity' => $quantity]);
    }

    public function create(CartItemDto $data): CartItemResponseDto
    {
        $cartItem = CartItem::create($data->toArray());

        return CartItemResponseDto::fromModel($cartItem);
    }

    public function update(int $id, CartItemDto $data): ?CartItemResponseDto
    {
        $cartItem = CartItem::findOrFail($id);

        $cartItem->update($data->toArray());

        return CartItemResponseDto::fromModel($cartItem->fresh());
    }

    public function delete(int $id): bool
    {
        return (bool) CartItem::destroy($id);
    }
}
