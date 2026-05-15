<?php

declare(strict_types=1);

namespace App\Services\Cart;

final readonly class UpdateCartService
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
     * Updates the exact quantity of a specific book in the current active cart.
     */
    public function execute(int $bookId, int $quantity): void
    {
        $this->cartResolverService->resolve()->update($bookId, $quantity);
    }
}
