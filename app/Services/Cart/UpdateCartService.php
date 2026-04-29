<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class UpdateCartService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCartService,
    ) {
    }

    public function execute(int $bookId, int $quantity): void
    {
        if (Auth::check()) {
            $cartItem = $this->repository->findByUserAndBook((int) Auth::id(), $bookId);

            if ($cartItem !== null) {
                $this->repository->updateQuantity($cartItem->id, $quantity);
            }
        } else {
            $this->guestCartService->update($bookId, $quantity);
        }
    }
}
