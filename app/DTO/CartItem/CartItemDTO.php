<?php

declare(strict_types=1);

namespace App\DTO\CartItem;

use App\Http\Requests\CartItemRequest;

final readonly class CartItemDTO
{
    public function __construct(
        public int $user_id,
        public int $book_id,
        public string $quantity,
    ) {}

    public static function fromRequest(CartItemRequest $request): self
    {
        return new self(
            user_id: $request->integer('user_id'),
            book_id: $request->integer('book_id'),
            quantity: (string) $request->string('quantity'),
        );
    }
}
