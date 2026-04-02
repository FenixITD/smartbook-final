<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Models\CartItem;
use Illuminate\Http\RedirectResponse;

final readonly class RemoveFromCartWebController
{
    public function __invoke(CartItem $cartItem): RedirectResponse
    {
        $cartItem->delete();

        return back()->with('success', 'Item removed from cart.');
    }
}
