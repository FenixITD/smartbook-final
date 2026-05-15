<?php

declare(strict_types=1);

namespace App\Services\Cart;

final readonly class AddToCartService
{
    public function __construct(
        private CartResolverService $cartResolverService,
    ) {
    }

    /**
     * @param int $bookId
     * @param int $quantity
     * @return void
     *
     * Adds a specified quantity of a book to the active cart (guest session or authenticated user).
     */
    public function execute(int $bookId, int $quantity = 1): void
    {
        $this->cartResolverService->resolve()->add($bookId, $quantity);
    }
}
