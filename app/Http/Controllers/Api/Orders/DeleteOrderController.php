<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Orders;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/orders/{order}',
    summary: 'Delete order by ID',
    security: [['bearerAuth' => []]],
    tags: ['Orders'],
    parameters: [
        new OA\Parameter(
            name: 'order',
            description: 'Delete a single order by ID',
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
            description: 'Delete a single order by ID',
            content: [],
        ),
    ]
)]
final readonly class DeleteOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $orderId): JsonResponse
    {
        $this->repository->delete($orderId);

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }
}
