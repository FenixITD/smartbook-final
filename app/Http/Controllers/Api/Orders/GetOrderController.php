<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Orders;

use App\Http\Resources\Order\OrderResource;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $orderId): JsonResponse
    {
        $order = $this->repository->getById($orderId);

        return (new OrderResource($order))->response();
    }
}
