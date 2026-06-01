<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class RemoveCartItemService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {
    }

    public function remove(int $bookId): void
    {
        $this->repository->deleteByUserAndBook((int) Auth::id(), $bookId);

        activity('CartItem')
            ->withProperties(['user_id' => Auth::id(), 'book_id' => $bookId])
            ->log('deleted');
    }
}
