<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\OrderItems;

use App\Http\Requests\OrderItem\OrderItemListRequest;
use App\Http\Resources\OrderItem\OrderItemResource;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/orderItems',
    summary: 'Get a list of all orderItems',
    security: [['bearerAuth' => []]],
    tags: ['OrderItems'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get a list of all orderItems',
            content: new OA\JsonContent(
                ref: '#/components/schemas/OrderItemResource'
            )
        ),
    ]
)]
final readonly class GetListOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(OrderItemListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $orderItems = $this->repository->getList($filters);

        return OrderItemResource::collection($orderItems)->response();
    }
}
