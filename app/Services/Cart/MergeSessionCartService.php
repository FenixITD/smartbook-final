<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class MergeSessionCartService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCart,
    ) {}

    public function execute(): void
    {
        $cart = $this->guestCart->all();
        if (empty($cart)) {
            return;
        }

        foreach ($cart as $item) {
            $this->repository->addOrIncrement(new CartItemDto(
                userId: Auth::id(),
                bookId: $item['book_id'],
                quantity: $item['quantity'],
            ));
        }

        $this->guestCart->clear();
    }
}
