<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemFiltersDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Dto\CartItem\CartItemWithBookResponseDto;
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

    public function getTotalByUserId(int $userId): string
    {
        $sum = CartItem::query()
            ->join('books', 'cart_items.book_id', '=', 'books.id')
            ->where('cart_items.user_id', $userId)
            ->sum(DB::raw('books.price * cart_items.quantity'));

        return bcadd('0', (string) $sum, 2);
    }

    public function getAllByUserId(int $userId): array
    {
        return CartItem::with('book.author')
            ->where('user_id', $userId)
            ->get()
            ->map(static fn (CartItem $item) => CartItemWithBookResponseDto::fromModel($item))
            ->all();
    }

    public function deleteByUserId(int $userId): void
    {
        CartItem::where('user_id', $userId)->delete();
    }

    public function addOrIncrement(CartItemDto $data): void
    {
        CartItem::upsert(
            [
                [
                    'user_id' => $data->userId,
                    'book_id' => $data->bookId,
                    'quantity' => $data->quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ],
            ['user_id', 'book_id'],
            ['quantity' => DB::raw('cart_items.quantity + EXCLUDED.quantity')]
        );
    }

    public function bulkAddOrIncrement(int $userId, array $items): void
    {
        if ($items === []) {
            return;
        }

        $now = now();
        $upsertData = array_map(static fn (array $item): array => [
            'user_id' => $userId,
            'book_id' => $item['book_id'],
            'quantity' => $item['quantity'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $items);

        CartItem::upsert(
            $upsertData,
            ['user_id', 'book_id'],
            ['quantity' => DB::raw('cart_items.quantity + EXCLUDED.quantity')]
        );
    }

    public function create(CartItemDto $data): CartItemResponseDto
    {
        /** @var CartItem $cartItem */
        $cartItem = CartItem::create($data->toArray());

        return CartItemResponseDto::fromModel($cartItem);
    }

    public function update(int $id, CartItemDto $data): CartItemResponseDto
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

    public function getQuantityByUserAndBook(int $userId, int $bookId): int
    {
        $value = CartItem::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->value('quantity');

        return is_numeric($value) ? (int) $value : 0;
    }
}
