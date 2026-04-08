<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\OrderItems;

use App\Http\Requests\OrderItem\OrderItemDataRequest;
use App\Http\Resources\OrderItem\OrderItemResource;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

readonly class CreateOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository
    ) {}

    public function __invoke(OrderItemDataRequest $request): JsonResponse
    {
        $orderItem = $this->repository->create($request->toDto());

        return (new OrderItemResource($orderItem))->response()->setStatusCode(201);
    }
}
