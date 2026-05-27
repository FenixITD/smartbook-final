<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\Book\BookResponseDto;
use App\Dto\CartItem\CartItemWithBookResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;

use function is_array;

final readonly class GuestCartService
{
    private const SESSION_KEY = 'guest_cart';

    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
    }

    /**
     * @return array<int, array{book_id: int, quantity: int}>
     *
     * Retrieves all raw cart items currently stored in the guest session.
     */
    public function getAll(): array
    {
        return $this->cart();
    }

    /**
     * @return array<CartItemWithBookResponseDto>
     *
     * Retrieves detailed cart items including associated book data for the guest session.
     */
    public function getItems(): array
    {
        $cart = $this->cart();

        if ($cart === []) {
            return [];
        }

        $books = $this->repository->findByIdsWithAuthor(array_keys($cart));

        /** @var array<int, BookResponseDto> $booksById */
        $booksById = collect($books)
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

    /**
     * @return float
     *
     * Calculates the total price of all items currently in the guest session cart.
     */
    public function getTotal(): float
    {
        $cart = $this->cart();

        if ($cart === []) {
            return 0.0;
        }

        $quantitiesByBookId = array_column($cart, 'quantity', 'book_id');

        return $this->repository->getTotalByIdsAndQuantities($quantitiesByBookId);
    }

    /**
     * @param int $bookId
     * @param int $quantity
     * @return void
     *
     * Adds a new book or increments its quantity in the guest session cart.
     */
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

    /**
     * @param int $bookId
     * @param int $quantity
     * @return void
     *
     * Updates the exact quantity of a specific book in the guest session cart.
     */
    public function update(int $bookId, int $quantity): void
    {
        $cart = $this->cart();

        if (!isset($cart[$bookId])) {
            return;
        }

        $cart[$bookId]['quantity'] = $quantity;
        session([self::SESSION_KEY => $cart]);
    }

    /**
     * @param int $bookId
     * @return void
     *
     * Removes a specific book entirely from the guest session cart.
     */
    public function remove(int $bookId): void
    {
        $cart = $this->cart();
        unset($cart[$bookId]);
        session([self::SESSION_KEY => $cart]);
    }

    /**
     * @return void
     *
     * Empties all items from the guest session cart.
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * @return int
     *
     * Returns the total number of individual items currently in the guest session cart.
     */
    public function count(): int
    {
        return array_sum(array_column($this->cart(), 'quantity'));
    }

    /**
     * @return array<int, array{book_id: int, quantity: int}>
     *
     * Retrieves the raw cart array directly from the session.
     */
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
