<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Http\Requests\CartItem\UpdateCartWebRequest;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Http\RedirectResponse;

final readonly class UpdateCartItemWebController
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function __invoke(UpdateCartWebRequest $request, int $bookId): RedirectResponse
    {
        $cartItem = auth()->check()
            ? CartItem::where('user_id', auth()->id())->where('book_id', $bookId)->first()
            : null;

        if ($cartItem) {
            abort_if($cartItem->user_id !== auth()->id(), 403);
        }

        $this->cartService->update(
            bookId: $bookId,
            quantity: $request->integer('quantity'),
            cartItem: $cartItem,
        );

        return back()->with('success', 'Cart updated.');
    }
}
