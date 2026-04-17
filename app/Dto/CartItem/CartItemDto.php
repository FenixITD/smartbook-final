<?php

declare(strict_types=1);

namespace App\Dto\CartItem;

final readonly class CartItemDto
{
    public function __construct(
        public int $userId,
        public int $bookId,
        public int $quantity,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'book_id' => $this->bookId,
            'quantity' => $this->quantity,
        ];
    }
}
