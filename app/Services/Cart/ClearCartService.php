<?php

declare(strict_types=1);

namespace App\Services\Cart;

final readonly class ClearCartService
{
    public function __construct(
        private CartResolverService $cartResolverService,
    ) {
    }

    public function execute(): void
    {
        $this->cartResolverService->resolve()->clear();
    }
}
