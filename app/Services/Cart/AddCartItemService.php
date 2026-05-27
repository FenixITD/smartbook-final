<?php

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class AddCartItemService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {}

    public function add(int $bookId, int $quantity): void
    {
        $this->repository->addOrIncrement(new CartItemDto(
            userId: (int) Auth::id(),
            bookId: $bookId,
            quantity: $quantity,
        ));

        activity('CartItem')
            ->withProperties(['user_id' => Auth::id(), 'book_id' => $bookId, 'quantity' => $quantity])
            ->log('added');
    }
}
