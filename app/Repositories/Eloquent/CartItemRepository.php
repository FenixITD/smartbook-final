<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemFiltersDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Collection;

final class CartItemRepository implements CartItemRepositoryInterface
{
    /** @return array<CartItemResponseDto> */
    public function getList(CartItemFiltersDto $filters): array
    {
        return CartItem::query()
            ->when($filters->id !== null, static fn ($q) => $q->where('id', $filters->id))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (CartItem $cartItem) => CartItemResponseDto::fromModel($cartItem))
            ->all();
    }

    public function getById(int $id): CartItemResponseDto|null
    {
        $cartItem = CartItem::find($id);

        return $cartItem !== null ? CartItemResponseDto::fromModel($cartItem) : null;
    }

    public function findByUserAndBook(int $userId, int $bookId): CartItem|null
    {
        return CartItem::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->first();
    }

    /** @return Collection<int, CartItem> */
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

        if ($existing !== null) {
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
        /** @var CartItem $cartItem */
        $cartItem = CartItem::create($data->toArray());

        return CartItemResponseDto::fromModel($cartItem);
    }

    public function update(int $id, CartItemDto $data): CartItemResponseDto|null
    {
        $cartItem = CartItem::findOrFail($id);

        $cartItem->update($data->toArray());

        /** @var CartItem $fresh */
        $fresh = $cartItem->fresh();

        return CartItemResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) CartItem::destroy($id);
    }
}
