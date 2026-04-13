<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Services\Cart\RemoveFromCartService;
use Illuminate\Http\RedirectResponse;

final readonly class RemoveFromCartWebController
{
    public function __construct(
        private RemoveFromCartService $service,
    ) {}

    public function __invoke(int $bookId): RedirectResponse
    {
        $this->service->execute(bookId: $bookId);

        return back()->with('success', 'Item removed from cart.');
    }
}
