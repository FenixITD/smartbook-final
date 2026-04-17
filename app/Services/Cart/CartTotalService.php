<?php

declare(strict_types=1);

namespace App\Services\Cart;

use function is_object;

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
                $price = is_object($item) && isset($item->book) && is_object($item->book)
                    ? $item->book->price
                    : 0.0;
                $qty = is_object($item) && isset($item->quantity) ? $item->quantity : 0;

                return $price * $qty;
            });
    }
}
