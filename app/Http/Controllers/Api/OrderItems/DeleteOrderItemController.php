<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\OrderItems;

use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $orderItem): JsonResponse
    {
        if (OrderItem::find($orderItem) === null) {
            return response()->json(['message' => 'OrderItem not found'], 404);
        }

        $this->repository->delete($orderItem);

        return response()->json([
            'message' => 'OrderItem deleted successfully',
        ]);
    }
}
