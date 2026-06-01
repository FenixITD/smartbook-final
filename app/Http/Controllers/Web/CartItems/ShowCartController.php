<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Cart\GuestCartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final readonly class ShowCartController
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCartService,
    ) {
    }

    public function __invoke(): View
    {
        if (Auth::check()) {
            $cartItems = $this->repository->getAllByUserId((int) Auth::id());
            $total = $this->repository->getTotalByUserId((int) Auth::id());
        } else {
            $cartItems = $this->guestCartService->getItems();
            $total = $this->guestCartService->getTotal();
        }

        return view('cart.index', compact('cartItems', 'total'));
    }
}
