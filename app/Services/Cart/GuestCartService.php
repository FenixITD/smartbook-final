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
        /** @var array<int, array{book_id: int, quantity: int}> $cart */
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
        /** @var array<int, array{book_id: int, quantity: int}> $cart */
        $cart = session(self::SESSION_KEY, []);

        if (isset($cart[$bookId])) {
            $cart[$bookId]['quantity'] = $quantity;
            session([self::SESSION_KEY => $cart]);
        }
    }

    public function remove(int $bookId): void
    {
        /** @var array<int, array{book_id: int, quantity: int}> $cart */
        $cart = session(self::SESSION_KEY, []);
        unset($cart[$bookId]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /** @return Collection<int, mixed> */
    public function getItems(): Collection
    {
        /** @var array<int, array{book_id: int, quantity: int}> $cart */
        $cart = session(self::SESSION_KEY, []);

        if ($cart === []) {
            return collect();
        }

        $books = Book::with('author')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        return collect($cart)
            ->map(static fn (array $item) => (object) [
                'id' => null,
                'book_id' => $item['book_id'],
                'quantity' => $item['quantity'],
                'book' => $books->get($item['book_id']),
                'user_id' => null,
            ])
            ->filter(static fn (mixed $item) => $item->book !== null)
            ->values();
    }

    public function count(): int
    {
        /** @var array<int, array{quantity: int}> $cart */
        $cart = session(self::SESSION_KEY, []);

        return (int) array_sum(array_column($cart, 'quantity'));
    }

    /** @return array<int, array{book_id: int, quantity: int}> */
    public function all(): array
    {
        /** @var array<int, array{book_id: int, quantity: int}> $cart */
        return session(self::SESSION_KEY, []);
    }
}
