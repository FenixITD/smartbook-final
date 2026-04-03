<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Services\Cart\CartService;
use Illuminate\View\View;

final readonly class ShowCartWebController
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function __invoke(): View
    {
        $cartItems = $this->cartService->getItems();
        $total = $this->cartService->total();

        return view('cart.index', compact('cartItems', 'total'));
    }
}
