<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Services\Cart\ClearCartService;
use Illuminate\Http\RedirectResponse;

final readonly class ClearCartWebController
{
    public function __construct(
        private ClearCartService $clearCartService,
    ) {}

    public function __invoke(): RedirectResponse
    {
        $this->clearCartService->execute();

        return back()->with('success', 'Cart cleared.');
    }
}
