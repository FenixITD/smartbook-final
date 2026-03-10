<?php

declare(strict_types=1);

namespace App\DTO\CartItem;

use App\Http\Requests\CartItem\CartItemDataRequest;

final readonly class CartItemDTO
{
    public function __construct(
        public int $userId,
        public int $bookId,
        public string $quantity,
    ) {}

    public static function fromRequest(CartItemDataRequest $request): self
    {
        return new self(
            userId: $request->integer('userId'),
            bookId: $request->integer('bookId'),
            quantity: (string) $request->string('quantity'),
        );
    }
}
