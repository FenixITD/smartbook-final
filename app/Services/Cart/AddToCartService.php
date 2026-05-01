<?php

declare(strict_types=1);

namespace App\Services\Cart;

final readonly class AddToCartService
{
    public function __construct(
        private CartResolverService $cartResolverService,
    ) {}

    public function execute(int $bookId, int $quantity = 1): void
    {
        $this->cartResolverService->resolve()->add($bookId, $quantity);
    }
}
