<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\OrderItems;

use App\Http\Requests\OrderItem\OrderItemDataRequest;
use App\Http\Resources\OrderItem\OrderItemResource;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

readonly class UpdateOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(OrderItemDataRequest $request, int $orderItemId): JsonResponse
    {
        $updatedOrderItem = $this->repository->update($orderItemId, $request->toDto());

        return (new OrderItemResource($updatedOrderItem))->response();
    }
}
