<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Services\Cart\CartResolverService;
use Illuminate\View\View;

final readonly class ShowCartController
{
    public function __construct(
        private CartResolverService $cartResolverService,
    ) {
    }

    public function __invoke(): View
    {
        $cartItems = $this->cartResolverService->resolve()->getItems();
        $total = $this->cartResolverService->resolve()->getTotal();

        return view('cart.index', compact('cartItems', 'total'));
    }
}
