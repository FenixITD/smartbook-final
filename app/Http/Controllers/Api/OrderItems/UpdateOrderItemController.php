<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\OrderItems;

use App\Http\Controllers\Api\Traits\LogsApiActivity;
use App\Http\Requests\OrderItem\OrderItemDataRequest;
use App\Http\Resources\OrderItem\OrderItemResource;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/orderItems/{orderItem}',
    summary: 'Update orderItem by ID',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/OrderItemDataRequest'
        ),
    ),
    tags: ['OrderItems'],
    parameters: [
        new OA\Parameter(
            name: 'orderItem',
            description: 'Update a single orderItem by ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
                example: 3,
            )
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update a single orderItem by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/OrderItemResource'
            )
        ),
    ]
)]
readonly class UpdateOrderItemController
{
    use LogsApiActivity;

    public function __construct(
        private OrderItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(OrderItemDataRequest $request, int $orderItemId): JsonResponse
    {
        $updatedOrderItem = $this->repository->update($orderItemId, $request->toDto());

        $this->logActivity('updated', 'order_items', $orderItemId, $request->validated());

        return (new OrderItemResource($updatedOrderItem))->response();
    }
}
