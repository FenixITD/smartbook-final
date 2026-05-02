<?php

declare(strict_types=1);

namespace App\Services\Cart;

final readonly class UpdateCartService
{
    public function __construct(
        private CartResolverService $cartResolverService,
    ) {
    }

    public function execute(int $bookId, int $quantity): void
    {
        $this->cartResolverService->resolve()->update($bookId, $quantity);
    }
}
