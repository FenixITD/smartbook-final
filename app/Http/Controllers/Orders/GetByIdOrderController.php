<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetByIdOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository
    ) {}

    public function __invoke(Order $order): JsonResponse
    {
        $orderId = $this->repository->getById($order->id);

        return (new OrderResource($orderId))->response();
    }
}
