<?php

declare(strict_types=1);

namespace App\Services\Cart;

final readonly class CartTotalService
{
    public function __construct(
        private CartResolverService $cartResolverService,
    ) {
    }

    /**
     * @return float
     *
     * Calculates and returns the total price of all items in the current active cart.
     */
    public function execute(): float
    {
        return $this->cartResolverService->resolve()->getTotal();
    }
}
