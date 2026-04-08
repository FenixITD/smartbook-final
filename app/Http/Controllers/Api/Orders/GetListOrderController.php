<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Orders;

use App\Http\Requests\Order\OrderListRequest;
use App\Http\Resources\Order\OrderResource;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository
    ) {}

    public function __invoke(OrderListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $orders = $this->repository->getList($filters);

        return OrderResource::collection($orders)->response();
    }
}
