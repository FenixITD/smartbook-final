<?php

declare(strict_types=1);

namespace App\Services\Cart;

final readonly class RemoveFromCartService
{
    public function __construct(
        private CartResolverService $cartResolverService,
    ) {}

    public function execute(int $bookId): void
    {
        $this->cartResolverService->resolve()->remove($bookId);
    }
}
