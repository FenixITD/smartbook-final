<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemFiltersDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Dto\CartItem\CartItemWithBookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\DB;

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

    public function getTotalByUserId(int $userId): float
    {
        return (float) CartItem::query()
            ->join('books', 'cart_items.book_id', '=', 'books.id')
            ->where('cart_items.user_id', $userId)
            ->sum(DB::raw('books.price * cart_items.quantity'));
    }

    public function findByUserAndBook(int $userId, int $bookId): CartItemResponseDto|null
    {
        $cartItem = CartItem::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->first();

        return $cartItem !== null ? CartItemResponseDto::fromModel($cartItem) : null;
    }

    public function getByUserId(int $userId, int $perPage): PaginatedResponseDto
    {
        $paginator = CartItem::with('book.author')
            ->where('user_id', $userId)
            ->paginate($perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    public function getAllByUserId(int $userId): array
    {
        return CartItem::with('book.author')
            ->where('user_id', $userId)
            ->get()
            ->map(static fn (CartItem $item) => CartItemWithBookResponseDto::fromModel($item))
            ->all();
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

    /**
     * @param array<int, array{book_id: int, quantity: int}> $items
     */
    public function bulkAddOrIncrement(int $userId, array $items): void
    {
        if ($items === []) {
            return;
        }

        $rows = array_map(
            static fn (array $item): array => [
                'user_id' => $userId,
                'book_id' => $item['book_id'],
                'quantity' => $item['quantity'],
            ],
            $items,
        );

        CartItem::upsert(
            $rows,
            uniqueBy: ['user_id', 'book_id'],
            update: ['quantity'],
        );
    }

    public function updateQuantity(int $id, int $quantity): void
    {
        CartItem::findOrFail($id)->update(['quantity' => $quantity]);
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

    public function updateByUserAndBook(int $userId, int $bookId, int $quantity): void
    {
        CartItem::where('user_id', $userId)->where('book_id', $bookId)
            ->update(['quantity' => $quantity]);
    }

    public function delete(int $id): bool
    {
        return (bool) CartItem::findOrFail($id)->delete();
    }

    public function deleteByUserAndBook(int $userId, int $bookId): void
    {
        CartItem::where('user_id', $userId)->where('book_id', $bookId)->delete();
    }
}
