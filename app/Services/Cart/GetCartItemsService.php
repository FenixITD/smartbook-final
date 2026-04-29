<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemWithBookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class GetCartItemsService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCartService,
    ) {
    }

    /** @return PaginatedResponseDto|array<CartItemWithBookResponseDto> */
    public function execute(int $perPage = 15): PaginatedResponseDto|array
    {
        if (Auth::check()) {
            return $this->repository->getByUserId((int) Auth::id(), $perPage);
        }

        return $this->guestCartService->getItems();
    }
}
