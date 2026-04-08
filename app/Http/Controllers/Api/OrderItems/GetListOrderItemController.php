<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\OrderItems;

use App\Http\Requests\OrderItem\OrderItemListRequest;
use App\Http\Resources\OrderItem\OrderItemResource;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository
    ) {}

    public function __invoke(OrderItemListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $orderItems = $this->repository->getList($filters);

        return OrderItemResource::collection($orderItems)->response();
    }
}
