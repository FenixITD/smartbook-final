<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class MergeSessionCartService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCartService,
    ) {
    }

    public function execute(): void
    {
        $cart = $this->guestCartService->all();

        if ($cart === []) {
            return;
        }

        $this->repository->bulkAddOrIncrement((int) Auth::id(), $cart);

        $this->guestCartService->clear();
    }
}
