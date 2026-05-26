<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Services\Cart\CartResolverService;
use Illuminate\Http\RedirectResponse;

final readonly class RemoveFromCartController
{
    public function __construct(
        private CartResolverService $service,
    ) {
    }

    public function __invoke(int $bookId): RedirectResponse
    {
        $this->service->resolve()->remove($bookId);

        return back()->with('success', 'Item removed from cart.');
    }
}
