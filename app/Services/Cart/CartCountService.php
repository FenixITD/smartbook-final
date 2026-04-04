<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

final readonly class CartCountService
{
    public function __construct(
        private GuestCartService $guestCart,
    ) {}

    public function execute(): int
    {
        if (Auth::check()) {
            return CartItem::where('user_id', Auth::id())->count();
        }

        return $this->guestCart->count();
    }
}
