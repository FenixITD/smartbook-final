<?php

declare(strict_types=1);

namespace App\Http\Controllers\OrderItems;

use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteOrderItemController
{
    public function __construct(
        private OrderItemRepositoryInterface $repository
    ) {}

    public function __invoke(OrderItem $orderItem): JsonResponse
    {
        $this->repository->delete($orderItem);

        return response()->json([
            'message' => 'OrderItem deleted successfully',
        ]);
    }
}
