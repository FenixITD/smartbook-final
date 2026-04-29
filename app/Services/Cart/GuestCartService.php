<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\Book\BookResponseDto;
use App\Dto\CartItem\CartItemWithBookResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;

use function is_array;

final class GuestCartService
{
    private const SESSION_KEY = 'guest_cart';

    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
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

    /** @return array<CartItemWithBookResponseDto> */
    public function getItems(): array
    {
        $cart = $this->cart();

        if ($cart === []) {
            return [];
        }

        $paginated = $this->repository->getByIdsWithAuthor(array_keys($cart), count($cart));

        /** @var array<int, BookResponseDto> $booksById */
        $booksById = collect($paginated->items)
            ->keyBy(static fn (BookResponseDto $book) => $book->id)
            ->all();

        $items = [];

        foreach ($cart as $item) {
            $book = $booksById[$item['book_id']] ?? null;

            if ($book === null) {
                continue;
            }

            $items[] = CartItemWithBookResponseDto::fromGuest(
                bookId: $item['book_id'],
                quantity: $item['quantity'],
                book: $book,
            );
        }

        return $items;
    }

    public function count(): int
    {
        return array_sum(array_column($this->cart(), 'quantity'));
    }

    /** @return array<int, array{book_id: int, quantity: int}> */
    public function getAll(): array
    {
        return $this->cart();
    }

    /** @return array<int, array{book_id: int, quantity: int}> */
    private function cart(): array
    {
        $raw = session(self::SESSION_KEY, []);

        if (!is_array($raw)) {
            return [];
        }

        /** @var array<int, array{book_id: int, quantity: int}> $raw */
        return $raw;
    }
}
