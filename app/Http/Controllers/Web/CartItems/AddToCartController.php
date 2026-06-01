<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Http\Requests\CartItem\AddToCartWebRequest;
use App\Services\Cart\AddCartItemService;
use App\Services\Cart\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final readonly class AddToCartController
{
    public function __construct(
        private AddCartItemService $authService,
        private GuestCartService $guestService,
    ) {
    }

    public function __invoke(AddToCartWebRequest $request): RedirectResponse
    {
        $service = Auth::check() ? $this->authService : $this->guestService;

        $service->add(
            bookId: $request->integer('book_id'),
            quantity: $request->integer('quantity', 1),
        );

        return back()->with('success', 'Book added to cart.');
    }
}
