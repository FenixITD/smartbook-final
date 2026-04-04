<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Http\Requests\CartItem\AddToCartWebRequest;
use App\Services\Cart\AddToCartService;
use Illuminate\Http\RedirectResponse;

final readonly class AddToCartWebController
{
    public function __construct(
        private AddToCartService $addToCartService,
    ) {}

    public function __invoke(AddToCartWebRequest $request): RedirectResponse
    {
        $this->addToCartService->execute(
            bookId: $request->integer('book_id'),
            quantity: $request->integer('quantity', 1),
        );

        return back()->with('success', 'Book added to cart.');
    }
}
