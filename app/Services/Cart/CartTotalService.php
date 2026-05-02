<?php

declare(strict_types=1);

namespace App\Services\Cart;

final readonly class CartTotalService
{
    public function __construct(
        private CartResolverService $cartResolverService,
    ) {
    }

    public function execute(): float
    {
        return $this->cartResolverService->resolve()->getTotal();
    }
}
