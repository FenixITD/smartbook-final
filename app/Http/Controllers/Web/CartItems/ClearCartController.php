<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Cart\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final readonly class ClearCartController
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCartService,
    ) {}

    public function __invoke(): RedirectResponse
    {
        if (Auth::check()) {
            $this->repository->deleteByUserId((int) Auth::id());
        } else {
            $this->guestCartService->clear();
        }

        return back()->with('success', 'Cart cleared.');
    }
}
