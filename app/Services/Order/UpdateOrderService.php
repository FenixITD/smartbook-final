<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderResponseDto;
use App\Enums\OrderStatusEnum;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateOrderService
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function execute(int $orderId, OrderDto $dto): OrderResponseDto
    {
        $lock = Cache::lock("order_update_{$orderId}", 5);

        /** @var OrderResponseDto $response */
        $response = $lock->block(5, function () use ($orderId, $dto): OrderResponseDto {
            $order = $this->repository->getById($orderId);

            if ($order === null) {
                throw new NotFoundHttpException('Order not found.');
            }

            $currentStatus = OrderStatusEnum::tryFrom($order->status);
            $newStatus = OrderStatusEnum::tryFrom($dto->status);

            if ($currentStatus !== null && $newStatus !== null) {
                if (!$currentStatus->canTransitionTo($newStatus)) {
                    throw ValidationException::withMessages([
                        'status' => "Invalid state transition. Cannot change order status from '{$currentStatus->value}' to '{$newStatus->value}'."
                    ]);
                }
            }

            $updatedOrder = $this->repository->update($orderId, $dto);

            if ($updatedOrder === null) {
                throw new NotFoundHttpException('Order could not be updated.');
            }

            return $updatedOrder;
        });

        return $response;
    }
}
