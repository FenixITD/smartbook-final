<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\Book;
use Illuminate\Support\Collection;

final class GuestCartService
{
    private const SESSION_KEY = 'guest_cart';

    public function add(int $bookId, int $quantity): void
    {
        $cart = session(self::SESSION_KEY, []);
        if (isset($cart[$bookId])) {
            $cart[$bookId]['quantity'] += $quantity;
        } else {
            $cart[$bookId] = ['book_id' => $bookId, 'quantity' => $quantity];
        }
        session([self::SESSION_KEY => $cart]);
    }

    public function update(int $bookId, int $quantity): void
    {
        $cart = session(self::SESSION_KEY, []);
        if (isset($cart[$bookId])) {
            $cart[$bookId]['quantity'] = $quantity;
            session([self::SESSION_KEY => $cart]);
        }
    }

    public function remove(int $bookId): void
    {
        $cart = session(self::SESSION_KEY, []);
        unset($cart[$bookId]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function getItems(): Collection
    {
        $cart = session(self::SESSION_KEY, []);
        if (empty($cart)) {
            return collect();
        }

        $books = Book::with('author')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        return collect($cart)
            ->map(fn ($item) => (object) [
                'id' => null,
                'book_id' => $item['book_id'],
                'quantity' => $item['quantity'],
                'book' => $books->get($item['book_id']),
                'user_id' => null,
            ])
            ->filter(fn ($item) => $item->book !== null)
            ->values();
    }

    public function count(): int
    {
        return array_sum(array_column(session(self::SESSION_KEY, []), 'quantity'));
    }

    public function all(): array
    {
        return session(self::SESSION_KEY, []);
    }
}
