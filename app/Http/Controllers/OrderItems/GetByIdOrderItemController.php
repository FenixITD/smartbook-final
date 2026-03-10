<?php

declare(strict_types=1);

namespace App\Http\Controllers\OrderItems;

use App\Http\Resources\OrderItem\OrderItemResource;
use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetByIdOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository
    ) {}

    public function __invoke(OrderItem $orderItem): JsonResponse
    {
        $orderItemId = $this->repository->getById($orderItem->id);

        return (new OrderItemResource($orderItemId))->response();
    }
}
