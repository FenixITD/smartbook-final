<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Services\Order\DeleteOrderService;
use Illuminate\Http\RedirectResponse;

final readonly class DeleteOrderController
{
    public function __construct(
        private DeleteOrderService $deleteOrderService,
    ) {
    }

    public function __invoke(int $orderId): RedirectResponse
    {
        $this->deleteOrderService->execute($orderId);

        return redirect()->route('orders.index')
            ->with('success', 'Order deleted successfully.');
    }
}
