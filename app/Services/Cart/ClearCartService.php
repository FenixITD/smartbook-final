<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

final readonly class ClearCartService
{
    public function __construct(
        private GuestCartService $guestCart,
    ) {}

    public function execute(): void
    {
        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())->delete();
        } else {
            $this->guestCart->clear();
        }
    }
}
