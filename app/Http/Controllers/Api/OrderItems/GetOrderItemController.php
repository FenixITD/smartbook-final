<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\OrderItems;

use App\Http\Resources\OrderItem\OrderItemResource;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/orderItems/{orderItem}',
    summary: 'Get orderItem by ID',
    tags: ['OrderItems'],
    parameters: [
        new OA\Parameter(
            name: 'orderItem',
            description: 'Get a single orderItem by ID',
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
            description: 'Get a single orderItem by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/OrderItemResource'
            )
        ),
    ]
)]
final readonly class GetOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $orderItemId): JsonResponse
    {
        $orderItem = $this->repository->getById($orderItemId);

        return (new OrderItemResource($orderItem))->response();
    }
}
