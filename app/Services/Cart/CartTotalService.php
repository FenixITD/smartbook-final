<?php

declare(strict_types=1);

namespace App\Services\Cart;

use stdClass;

final readonly class CartTotalService
{
    public function __construct(
        private GetCartItemsService $getCartItems,
    ) {
    }

    public function execute(): float
    {
        return $this->getCartItems->execute()
            ->sum(static function (mixed $item): float {
                /** @var object{id: null, book_id: int, quantity: int, book: \App\Models\Book|null, user_id: null}&stdClass $item */
                $book = $item->book;
                $price = $book !== null ? $book->price : 0.0;

                return $price * $item->quantity;
            });
    }
}
