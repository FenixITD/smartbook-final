<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\OrderItems;

use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/orderItems/{orderItem}',
    summary: 'Delete orderItem by ID',
    security: [['bearerAuth' => []]],
    tags: ['OrderItems'],
    parameters: [
        new OA\Parameter(
            name: 'orderItem',
            description: 'Delete a single orderItem by ID',
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
            description: 'Delete a single orderItem by ID',
            content: [],
        ),
    ]
)]
final readonly class DeleteOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $orderItemId): JsonResponse
    {
        $this->repository->delete($orderItemId);

        return response()->json([
            'message' => 'OrderItem deleted successfully',
        ]);
    }
}
