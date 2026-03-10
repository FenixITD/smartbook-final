<?php

declare(strict_types=1);

namespace App\Http\Controllers\OrderItems;

use App\DTO\OrderItem\OrderItemDTO;
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
        $dto = OrderItemDTO::fromRequest($request);
        $updated = $this->service->execute($orderItem, $dto);

        return (new OrderItemResource($updated))->response();
    }
}
