<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository
    ) {}

    public function __invoke(Order $order): JsonResponse
    {
        $this->repository->delete($order);

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }
}
