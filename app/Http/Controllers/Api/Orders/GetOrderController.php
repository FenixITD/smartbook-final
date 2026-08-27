<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Orders;

use App\Http\Resources\Order\OrderResource;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/orders/{order}',
    summary: 'Get order by ID',
    security: [['bearerAuth' => []]],
    tags: ['Orders'],
    parameters: [
        new OA\Parameter(
            name: 'order',
            description: 'Get a single order by ID',
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
            description: 'Get a single order by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/OrderResource'
            )
        ),
    ]
)]
final readonly class GetOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $orderId): JsonResponse
    {
        $order = $this->repository->getById($orderId);

        if ($order === null) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return (new OrderResource($order))->response();
    }
}
