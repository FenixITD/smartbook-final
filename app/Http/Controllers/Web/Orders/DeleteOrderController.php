<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\RedirectResponse;

final readonly class DeleteOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $orderId): RedirectResponse
    {
        $this->repository->delete($orderId);

        return redirect()->route('orders.index')
            ->with('success', 'Order deleted successfully.');
    }
}
