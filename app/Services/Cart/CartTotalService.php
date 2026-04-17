<?php

declare(strict_types=1);

namespace App\Services\Cart;

final readonly class CartTotalService
{
    public function __construct(
        private GetCartItemsService $getCartItems,
    ) {
    }

    public function execute(): float
    {
        return (float) $this->getCartItems->execute()
            ->sum(static function (mixed $item): float {
                $price = is_object($item) && isset($item->book) && is_object($item->book)
                    ? (float) $item->book->price
                    : 0.0;
                $qty = is_object($item) && isset($item->quantity) ? (int) $item->quantity : 0;

                return $price * $qty;
            });
    }
}
