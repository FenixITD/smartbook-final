<?php

declare(strict_types=1);

namespace App\Http\Controllers\OrderItems;

use App\DTO\OrderItem\OrderItemFiltersDTO;
use App\Http\Requests\OrderItem\OrderItemListRequest;
use App\Http\Resources\OrderItem\OrderItemCollection;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository
    ) {}

    public function __invoke(OrderItemListRequest $request): JsonResponse
    {
        $filters = OrderItemFiltersDTO::fromRequest($request);
        $orderItems = $this->repository->getList($filters);

        return (new OrderItemCollection($orderItems))->response();
    }
}
