<?php

declare(strict_types=1);

namespace App\Dto\CartItem;

use App\Dto\Book\BookResponseDto;
use App\Models\CartItem;

final readonly class CartItemWithBookResponseDto
{
    public static function fromModel(CartItem $cartItem): self
    {
        return new self(
            id: $cartItem->id,
            userId: $cartItem->user_id,
            bookId: $cartItem->book_id,
            quantity: $cartItem->quantity,
            book: $cartItem->relationLoaded('book') && $cartItem->book !== null
                ? BookResponseDto::fromModel($cartItem->book)
                : null,
        );
    }

    public static function fromGuest(int $bookId, int $quantity, BookResponseDto|null $book): self
    {
        return new self(
            id: null,
            userId: null,
            bookId: $bookId,
            quantity: $quantity,
            book: $book,
        );
    }

    public function __construct(
        public int|null $id,
        public int|null $userId,
        public int $bookId,
        public int $quantity,
        public BookResponseDto|null $book,
    ) {
    }
}
