<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

final readonly class GetCartItemsService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $service,
    ) {}

    public function execute(): Collection
    {
        if (Auth::check()) {
            return $this->repository->getByUserId(Auth::id());
        }

        return $this->service->getItems();
    }
}
