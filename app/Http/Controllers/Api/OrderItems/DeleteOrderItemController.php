<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\OrderItems;

use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $orderItemId): JsonResponse
    {
        $this->repository->delete($orderItemId);

        return response()->json([
            'message' => 'OrderItem deleted successfully',
        ]);
    }
}
