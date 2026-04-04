<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class RemoveFromCartService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCart,
    ) {}

    public function execute(int $bookId, ?CartItem $cartItem = null): void
    {
        if (Auth::check() && $cartItem) {
            $this->repository->delete($cartItem);
        } else {
            $this->guestCart->remove($bookId);
        }
    }
}
