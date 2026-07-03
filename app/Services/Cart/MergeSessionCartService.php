<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MergeSessionCartService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCartService,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function execute(): void
    {
        $cart = $this->guestCartService->getAll();

        if ($cart === []) {
            return;
        }

        $userId = (int) Auth::id();

        $lock = Cache::lock("merge_cart_{$userId}", 5);

        if (!$lock->get()) {
            return;
        }

        try {
            $this->transactionManager->transaction(function () use ($userId, $cart): void {
                $this->repository->bulkAddOrIncrement($userId, $cart);
            });

            $this->guestCartService->clear();
        } finally {
            $lock->release();
        }
    }
}
