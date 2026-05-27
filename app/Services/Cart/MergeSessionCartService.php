<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class MergeSessionCartService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCartService,
    ) {
    }

    /**
     * @return void
     *
     * Merges items from the guest session cart into the authenticated user's database cart upon login, then clears the session.
     */
    public function execute(): void
    {
        $cart = $this->guestCartService->getAll();

        if ($cart === []) {
            return;
        }

        $this->repository->bulkAddOrIncrement((int) Auth::id(), $cart);

        $this->guestCartService->clear();
    }
}
