<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\DTO\Order\OrderFiltersDTO;
use App\Http\Requests\Order\OrderListRequest;
use App\Http\Resources\Order\OrderCollection;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository
    ) {}

    public function __invoke(OrderListRequest $request): JsonResponse
    {
        $filters = OrderFiltersDTO::fromRequest($request);
        $orders = $this->repository->getList($filters);

        return (new OrderCollection($orders))->response();
    }
}
