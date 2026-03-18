<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Http\Requests\Order\OrderDataRequest;
use App\Http\Resources\Order\OrderResource;
use App\Services\Order\CreateOrderService;
use Illuminate\Http\JsonResponse;

readonly class CreateOrderController
{
    public function __construct(
        private CreateOrderService $service
    ) {}

    public function __invoke(OrderDataRequest $request): JsonResponse
    {
        $order = $this->service->execute($request->toDto());

        return (new OrderResource($order))->response()->setStatusCode(201);
    }
}
