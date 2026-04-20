<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Orders;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $orderId): JsonResponse
    {
        if ($this->repository->getById($orderId) === null) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $this->repository->delete($orderId);

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }
}
