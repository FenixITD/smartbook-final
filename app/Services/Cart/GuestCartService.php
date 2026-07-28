<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\Book\BookResponseDto;
use App\Dto\CartItem\CartItemWithBookResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class GuestCartService
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private GuestCartStorageInterface $guestCartStorage,
    ) {
    }

    /**
     * @return array<int, array{book_id: int, quantity: int}>
     */
    public function getAll(): array
    {
        return $this->guestCartStorage->getCart();
    }

    /**
     * @return array<CartItemWithBookResponseDto>
     */
    public function getItems(): array
    {
        $cart = $this->guestCartStorage->getCart();

        if ($cart === []) {
            return [];
        }

        $books = $this->bookRepository->findByIdsWithAuthor(array_keys($cart));

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

    public function getTotal(): string
    {
        $cart = $this->guestCartStorage->getCart();

        if ($cart === []) {
            return '0.00';
        }

        $quantitiesByBookId = array_column($cart, 'quantity', 'book_id');

        return $this->bookRepository->getTotalByIdsAndQuantities($quantitiesByBookId);
    }

    public function add(int $bookId, int $quantity): void
    {
        $this->validateQuantity($quantity);

        $book = $this->bookRepository->getById($bookId);
        if ($book === null) {
            throw ValidationException::withMessages(['cart' => 'Book not found.']);
        }

        $cart = $this->guestCartStorage->getCart();
        $currentQuantity = $cart[$bookId]['quantity'] ?? 0;

        if ($currentQuantity + $quantity > $book->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Cannot add more. Only {$book->stock} available in stock."
            ]);
        }

        $cart[$bookId]['quantity'] = $currentQuantity + $quantity;
        $cart[$bookId]['book_id'] = $bookId;

        $this->guestCartStorage->saveCart($cart);
    }

    public function update(int $bookId, int $quantity): void
    {
        $this->validateQuantity($quantity);

        $book = $this->bookRepository->getById($bookId);
        if ($book !== null && $quantity > $book->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Cannot update. Only {$book->stock} available in stock."
            ]);
        }

        $cart = $this->guestCartStorage->getCart();

        if (!isset($cart[$bookId])) {
            return;
        }

        $cart[$bookId]['quantity'] = $quantity;
        $this->guestCartStorage->saveCart($cart);
    }

    public function remove(int $bookId): void
    {
        $cart = $this->guestCartStorage->getCart();

        if (isset($cart[$bookId])) {
            unset($cart[$bookId]);
            $this->guestCartStorage->saveCart($cart);
        }
    }

    public function clear(): void
    {
        $this->guestCartStorage->clear();
    }

    public function count(): int
    {
        return array_sum(array_column($this->guestCartStorage->getCart(), 'quantity'));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be strictly positive.');
        }
    }
}
