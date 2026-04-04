<?php

declare(strict_types=1);

namespace App\Services\Cart;

final readonly class CartTotalService
{
    public function __construct(
        private GetCartItemsService $getCartItems,
    ) {}

    public function execute(): float
    {
        return $this->getCartItems->execute()
            ->sum(fn ($item) => $item->book->price * $item->quantity);
    }
}
