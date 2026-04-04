<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Services\Cart\CartTotalService;
use App\Services\Cart\GetCartItemsService;
use Illuminate\View\View;

final readonly class ShowCartWebController
{
    public function __construct(
        private GetCartItemsService $getCartItemsService,
        private CartTotalService $cartTotalService,
    ) {}

    public function __invoke(): View
    {
        $cartItems = $this->getCartItemsService->execute();
        $total = $this->cartTotalService->execute();

        return view('cart.index', compact('cartItems', 'total'));
    }
}
