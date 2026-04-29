<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Http\Requests\CartItem\UpdateCartWebRequest;
use App\Services\Cart\UpdateCartService;
use Illuminate\Http\RedirectResponse;

final readonly class UpdateCartItemController
{
    public function __construct(
        private UpdateCartService $updateCartService,
    ) {
    }

    public function __invoke(UpdateCartWebRequest $request, int $bookId): RedirectResponse
    {
        $this->updateCartService->execute(
            bookId: $bookId,
            quantity: $request->integer('quantity'),
        );

        return back()->with('success', 'Cart updated.');
    }
}
