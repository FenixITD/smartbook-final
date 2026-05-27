<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Orders;

use App\Http\Requests\Order\OrderListRequest;
use App\Http\Resources\Order\OrderResource;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/orders',
    summary: 'Get a list of all orders',
    security: [['bearerAuth' => []]],
    tags: ['Orders'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get a list of all orders',
            content: new OA\JsonContent(
                ref: '#/components/schemas/OrderResource'
            )
        ),
    ]
)]
final readonly class GetListOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function __invoke(OrderListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $orders = $this->repository->getList($filters);

        return OrderResource::collection($orders)->response();
    }
}
