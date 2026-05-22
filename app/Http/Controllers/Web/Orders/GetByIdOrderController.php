<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\View\View;

final readonly class GetByIdOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $orderId): View
    {
        $order = $this->repository->findByIdWithRelations($orderId);

        return view('orders.show', compact('order'));
    }
}
