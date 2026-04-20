<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class AddToCartService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCartService,
    ) {
    }

    public function execute(int $bookId, int $quantity = 1): void
    {
        if (Auth::check()) {
            $this->repository->addOrIncrement(new CartItemDto(
                userId: (int) Auth::id(),
                bookId: $bookId,
                quantity: $quantity,
            ));
        } else {
            $this->guestCartService->add($bookId, $quantity);
        }
    }
}
