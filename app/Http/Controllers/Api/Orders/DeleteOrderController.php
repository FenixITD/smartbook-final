<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Orders;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $order): JsonResponse
    {
        if (Order::find($order) === null) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $this->repository->delete($order);

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }
}
