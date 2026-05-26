<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Services\Cart\CartResolverService;
use Illuminate\Http\RedirectResponse;

final readonly class ClearCartController
{
    public function __construct(
        private CartResolverService $service,
    ) {
    }

    public function __invoke(): RedirectResponse
    {
        $this->service->resolve()->clear();

        return back()->with('success', 'Cart cleared.');
    }
}
