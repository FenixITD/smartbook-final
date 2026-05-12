<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Api\Traits\LogsApiActivity;
use App\Http\Requests\Order\OrderDataRequest;
use App\Http\Resources\Order\OrderResource;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/orders/{order}',
    summary: 'Update order by ID',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/OrderDataRequest'
        ),
    ),
    tags: ['Orders'],
    parameters: [
        new OA\Parameter(
            name: 'order',
            description: 'Update a single order by ID',
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
            description: 'Update a single order by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/OrderResource'
            )
        ),
    ]
)]
readonly class UpdateOrderController
{
    use LogsApiActivity;

    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function __invoke(OrderDataRequest $request, int $orderId): JsonResponse
    {
        $updatedOrder = $this->repository->update($orderId, $request->toDto());

        $this->logActivity('updated', 'orders', $orderId, $request->validated());

        return (new OrderResource($updatedOrder))->response();
    }
}
