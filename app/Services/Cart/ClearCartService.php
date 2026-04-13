<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class ClearCartService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $service,
    ) {}

    public function execute(): void
    {
        if (Auth::check()) {
            $this->repository->deleteByUserId(Auth::id());
        } else {
            $this->service->clear();
        }
    }
}
