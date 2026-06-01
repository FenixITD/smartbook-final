<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Services\Cart\GuestCartService;
use App\Services\Cart\RemoveCartItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final readonly class RemoveFromCartController
{
    public function __construct(
        private RemoveCartItemService $authService,
        private GuestCartService $guestService,
    ) {
    }

    public function __invoke(int $bookId): RedirectResponse
    {
        $service = Auth::check() ? $this->authService : $this->guestService;

        $service->remove($bookId);

        return back()->with('success', 'Item removed from cart.');
    }
}
