<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Models\CartItem;
use App\Services\Cart\RemoveFromCartService;
use Illuminate\Http\RedirectResponse;

final readonly class RemoveFromCartWebController
{
    public function __construct(
        private RemoveFromCartService $removeFromCartService,
    ) {}

    public function __invoke(int $bookId): RedirectResponse
    {
        $cartItem = auth()->check()
            ? CartItem::where('user_id', auth()->id())->where('book_id', $bookId)->first()
            : null;

        if ($cartItem) {
            abort_if($cartItem->user_id !== auth()->id(), 403);
        }

        $this->removeFromCartService->execute(bookId: $bookId, cartItem: $cartItem);

        return back()->with('success', 'Item removed from cart.');
    }
}
