<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\DTO\Order\OrderDTO;
use App\Http\Requests\Order\OrderDataRequest;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Services\Order\UpdateOrderService;
use Illuminate\Http\JsonResponse;

readonly class UpdateOrderController
{
    public function __construct(
        private UpdateOrderService $service
    ) {}

    public function __invoke(OrderDataRequest $request, Order $order): JsonResponse
    {
        $dto = OrderDTO::fromRequest($request);
        $updated = $this->service->execute($order, $dto);

        return (new OrderResource($updated))->response();
    }
}
