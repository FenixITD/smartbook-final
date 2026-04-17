<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class RemoveFromCartService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $service,
    ) {
    }

    public function execute(int $bookId): void
    {
        if (Auth::check()) {
            $cartItem = $this->repository->findByUserAndBook((int) Auth::id(), $bookId);

            if ($cartItem !== null) {
                $this->repository->delete($cartItem->id);
            }
        } else {
            $this->service->remove($bookId);
        }
    }
}
