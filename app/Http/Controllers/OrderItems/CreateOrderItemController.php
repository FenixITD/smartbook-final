<?php

declare(strict_types=1);

namespace App\Http\Controllers\OrderItems;

use App\Http\Requests\OrderItem\OrderItemDataRequest;
use App\Http\Resources\OrderItem\OrderItemResource;
use App\Services\OrderItem\CreateOrderItemService;
use Illuminate\Http\JsonResponse;

readonly class CreateOrderItemController
{
    public function __construct(
        private CreateOrderItemService $service
    ) {}

    public function __invoke(OrderItemDataRequest $request): JsonResponse
    {
        $orderItem = $this->service->execute($request->toDto());

        return (new OrderItemResource($orderItem))->response()->setStatusCode(201);
    }
}
