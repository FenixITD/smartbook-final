<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class CartCountService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $service,
    ) {}

    public function execute(): int
    {
        if (Auth::check()) {
            return $this->repository->countByUserId(Auth::id());
        }

        return $this->service->count();
    }
}
