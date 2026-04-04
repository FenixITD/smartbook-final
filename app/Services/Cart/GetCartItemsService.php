<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\CartItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

final readonly class GetCartItemsService
{
    public function __construct(
        private GuestCartService $guestCart,
    ) {}

    public function execute(): Collection
    {
        if (Auth::check()) {
            return CartItem::with('book.author')
                ->where('user_id', Auth::id())
                ->get();
        }

        return $this->guestCart->getItems();
    }
}
