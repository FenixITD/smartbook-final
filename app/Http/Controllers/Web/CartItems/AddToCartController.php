<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Http\Requests\CartItem\AddToCartWebRequest;
use App\Services\Cart\CartResolverService;
use Illuminate\Http\RedirectResponse;

final readonly class AddToCartController
{
    public function __construct(
        private CartResolverService $cartResolverService,
    ) {
    }

    public function __invoke(AddToCartWebRequest $request): RedirectResponse
    {
        $this->cartResolverService->resolve()->add(
                bookId: $request->integer('book_id'),
                quantity: $request->integer('quantity', 1)
            );

        return back()->with('success', 'Book added to cart.');
    }
}
