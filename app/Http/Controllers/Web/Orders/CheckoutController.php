<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Http\Requests\Order\CheckoutRequest;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Order\CreateOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final readonly class CheckoutController
{
    public function __construct(
        private CartItemRepositoryInterface $cartItemRepository,
        private CreateOrderService $createOrderService,
    ) {
    }

    public function create(): View
    {
        $cartItems = $this->cartItemRepository->getAllByUserId((int) Auth::id());
        $total = $this->cartItemRepository->getTotalByUserId((int) Auth::id());

        return view('orders.checkout', compact('cartItems', 'total'));
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $this->createOrderService->execute($request->toDto());

        return redirect()->route('dashboard')
            ->with('success', 'Order placed successfully.');
    }
}
