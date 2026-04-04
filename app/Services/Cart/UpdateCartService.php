<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class UpdateCartService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCart,
    ) {}

    public function execute(int $bookId, int $quantity, ?CartItem $cartItem = null): void
    {
        if (Auth::check() && $cartItem) {
            $this->repository->updateQuantity($cartItem, $quantity);
        } else {
            $this->guestCart->update($bookId, $quantity);
        }
    }
}
