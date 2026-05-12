<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Api\Traits\LogsApiActivity;
use App\Http\Requests\Order\OrderDataRequest;
use App\Http\Resources\Order\OrderResource;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/orders',
    summary: 'Create order',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/OrderDataRequest'
        ),
    ),
    tags: ['Orders'],
    responses: [
        new OA\Response(
            response: 201,
            description: 'Get created order',
            content: new OA\JsonContent(
                ref: '#/components/schemas/OrderResource'
            )
        ),
    ]
)]
readonly class CreateOrderController
{
    use LogsApiActivity;

    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function __invoke(OrderDataRequest $request): JsonResponse
    {
        $order = $this->repository->create($request->toDto());

        $this->logActivity('created', 'orders', $order->id, $request->validated());

        return (new OrderResource($order))->response()->setStatusCode(201);
    }
}
