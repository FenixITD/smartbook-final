<?php

declare(strict_types=1);

namespace App\Http\Controllers\OrderItems;

use App\Http\Requests\OrderItem\OrderItemDataRequest;
use App\Http\Resources\OrderItem\OrderItemResource;
use App\Models\OrderItem;
use App\Services\OrderItem\UpdateOrderItemService;
use Illuminate\Http\JsonResponse;

readonly class UpdateOrderItemController
{
    public function __construct(
        private UpdateOrderItemService $service
    ) {}

    public function __invoke(OrderItemDataRequest $request, OrderItem $orderItem): JsonResponse
    {
        $updated = $this->service->execute($orderItem, $request->toDto());

        return (new OrderItemResource($updated))->response();
    }
}
