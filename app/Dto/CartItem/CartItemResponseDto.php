<?php

declare(strict_types=1);

namespace App\Dto\CartItem;

use App\Models\CartItem;

final readonly class CartItemResponseDto
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $bookId,
        public int $quantity,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(CartItem $cartItem): self
    {
        return new self(
            id: $cartItem->id,
            userId: (int) $cartItem->user_id,
            bookId: (int) $cartItem->book_id,
            quantity: (int) $cartItem->quantity,
            createdAt: $cartItem->created_at->toDateTimeString(),
            updatedAt: $cartItem->updated_at->toDateTimeString(),
        );
    }
}
