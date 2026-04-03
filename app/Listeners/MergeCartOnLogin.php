<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Cart\CartService;
use Illuminate\Auth\Events\Login;

final readonly class MergeCartOnLogin
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function handle(Login $event): void
    {
        $this->cartService->mergeSessionCartToUser();
    }
}
