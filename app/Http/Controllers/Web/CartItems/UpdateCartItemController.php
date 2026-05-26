<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Http\Requests\CartItem\UpdateCartWebRequest;
use App\Services\Cart\CartResolverService;
use Illuminate\Http\RedirectResponse;

final readonly class UpdateCartItemController
{
    public function __construct(
        private CartResolverService $cartResolverService,
    ) {
    }

    public function __invoke(UpdateCartWebRequest $request, int $bookId): RedirectResponse
    {
        $this->cartResolverService->resolve()->update($bookId, $request->integer('quantity'));

        return back()->with('success', 'Cart updated.');
    }
}
