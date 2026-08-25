<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderResponseDto;
use App\Enums\OrderStatusEnum;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateOrderService
{
    public function __construct(
        private OrderRepositoryInterface $repository,
        private OrderItemRepositoryInterface $orderItemRepository,
        private BookRepositoryInterface $bookRepository,
        private TransactionManagerInterface $transactionManager,
    ) {}

    public function execute(int $orderId, OrderDto $dto): OrderResponseDto
    {
        $lock = Cache::lock("order_update_{$orderId}", 5);

        /** @var OrderResponseDto $response */
        $response = $lock->block(5, fn (): OrderResponseDto => $this->updateOrderInTransaction($orderId, $dto));

        return $response;
    }

    private function updateOrderInTransaction(int $orderId, OrderDto $dto): OrderResponseDto
    {
        /** @var OrderResponseDto $orderResponse */
        $orderResponse = $this->transactionManager->transaction(
            fn (): OrderResponseDto => $this->updateOrder($orderId, $dto)
        );

        return $orderResponse;
    }

    private function updateOrder(int $orderId, OrderDto $dto): OrderResponseDto
    {
        $order = $this->repository->getById($orderId);

        if ($order === null) {
            throw new NotFoundHttpException('Order not found.');
        }

        $currentStatus = OrderStatusEnum::tryFrom($order->status);
        $newStatus = OrderStatusEnum::tryFrom($dto->status);

        if ($currentStatus === null || $newStatus === null) {
            throw ValidationException::withMessages([
                'status' => 'Invalid order status value.',
            ]);
        }

        if (! $currentStatus->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => "Invalid state transition. Cannot change order status from '{$currentStatus->value}' to '{$newStatus->value}'.",
            ]);
        }

        $updatedOrder = $this->repository->update($orderId, $dto);

        if ($updatedOrder === null) {
            throw new NotFoundHttpException('Order could not be updated.');
        }

        if (
            $newStatus === OrderStatusEnum::Cancelled
            && $currentStatus !== null
            && $currentStatus !== OrderStatusEnum::Cancelled
        ) {
            $this->restoreStock($orderId);
        }

        return $updatedOrder;
    }

    private function restoreStock(int $orderId): void
    {
        foreach ($this->orderItemRepository->getAllByOrderId($orderId) as $item) {
            $this->bookRepository->incrementStock($item->bookId, $item->quantity);
        }
    }
}
