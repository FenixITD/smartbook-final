<?php

declare(strict_types=1);

namespace App\DTO\CartItem;

use App\Models\CartItem;
use Illuminate\Support\Collection;

final readonly class CartItemResponseDTO
{
    public function __construct(
        public int $id,
        public int $user_id,
        public int $book_id,
        public int $quantity,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(CartItem $cartItem): self
    {
        return new self(
            id: $cartItem->id,
            user_id: $cartItem->user_id,
            book_id: $cartItem->book_id,
            quantity: $cartItem->quantity,
            created_at: $cartItem->created_at->toDateTimeString(),
            updated_at: $cartItem->updated_at->toDateTimeString(),
        );
    }

    /**
     * @param  Collection<int, CartItem>  $cartItems
     * @return array<int, self>
     */
    public static function fromCollection(Collection $cartItems): array
    {
        return $cartItems->map(fn (CartItem $cartItem) => self::fromModel($cartItem))->all();
    }
}
