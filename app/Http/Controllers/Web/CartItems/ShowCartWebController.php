<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Models\CartItem;
use Illuminate\View\View;

final readonly class ShowCartWebController
{
    public function __invoke(): View
    {
        $cartItems = CartItem::with('book.author')
            ->where('user_id', auth()->id())
            ->get();

        $total = $cartItems->sum(fn ($item) => $item->book->price * $item->quantity);

        return view('cart.index', compact('cartItems', 'total'));
    }
}
