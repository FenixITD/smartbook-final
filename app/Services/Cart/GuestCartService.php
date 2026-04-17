<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\Book;
use Illuminate\Support\Collection;

final class GuestCartService
{
    private const SESSION_KEY = 'guest_cart';

    /** @return array<int, array{book_id: int, quantity: int}> */
    private function cart(): array
    {
        $raw = session(self::SESSION_KEY, []);

        return is_array($raw) ? $raw : [];
    }

    public function add(int $bookId, int $quantity): void
    {
        $cart = $this->cart();

        if (isset($cart[$bookId])) {
            $cart[$bookId]['quantity'] += $quantity;
        } else {
            $cart[$bookId] = ['book_id' => $bookId, 'quantity' => $quantity];
        }

        session([self::SESSION_KEY => $cart]);
    }

    public function update(int $bookId, int $quantity): void
    {
        $cart = $this->cart();

        if (isset($cart[$bookId])) {
            $cart[$bookId]['quantity'] = $quantity;
            session([self::SESSION_KEY => $cart]);
        }
    }

    public function remove(int $bookId): void
    {
        $cart = $this->cart();
        unset($cart[$bookId]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /** @return Collection<int, object{id: null, book_id: int, quantity: int, book: Book|null, user_id: null}> */
    public function getItems(): Collection
    {
        $cart = $this->cart();

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
        return array_sum(array_column($this->cart(), 'quantity'));
    }

    /** @return array<int, array{book_id: int, quantity: int}> */
    public function all(): array
    {
        return $this->cart();
    }
}
