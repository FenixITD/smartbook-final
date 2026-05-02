<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\OrderItems;

use App\Http\Requests\OrderItem\OrderItemDataRequest;
use App\Http\Resources\OrderItem\OrderItemResource;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/orderItems',
    summary: 'Create orderItem',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/OrderItemDataRequest'
        ),
    ),
    tags: ['OrderItems'],
    responses: [
        new OA\Response(
            response: 201,
            description: 'Get created orderItem',
            content: new OA\JsonContent(
                ref: '#/components/schemas/OrderItemResource'
            )
        ),
    ]
)]
readonly class CreateOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(OrderItemDataRequest $request): JsonResponse
    {
        $orderItem = $this->repository->create($request->toDto());

        return (new OrderItemResource($orderItem))->response()->setStatusCode(201);
    }
}
