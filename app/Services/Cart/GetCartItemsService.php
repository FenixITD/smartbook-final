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
    ) {
    }

    /** @return Collection<int, mixed> */
    public function execute(): Collection
    {
        if (Auth::check()) {
            /** @var Collection<int, mixed> $result */
            return $this->repository->getByUserId((int) Auth::id());
        }

        return $this->service->getItems();
    }
}
