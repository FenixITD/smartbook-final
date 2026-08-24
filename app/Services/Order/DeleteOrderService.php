<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Enums\OrderStatusEnum;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteOrderService
{
    /** Order statuses whose items still reserve stock. */
    private const STOCK_RESERVING_STATUSES = [
        OrderStatusEnum::Pending,
        OrderStatusEnum::Paid,
        OrderStatusEnum::Shipped,
    ];

    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private OrderItemRepositoryInterface $orderItemRepository,
        private BookRepositoryInterface $bookRepository,
        private TransactionManagerInterface $transactionManager,
    ) {}

    public function execute(int $orderId): void
    {
        $lock = Cache::lock("order_delete_{$orderId}", 5);

        $lock->block(5, function () use ($orderId): void {
            $this->transactionManager->transaction(function () use ($orderId): void {
                $this->deleteOrder($orderId);
            });
        });
    }

    private function deleteOrder(int $orderId): void
    {
        $order = $this->orderRepository->getById($orderId);

        if ($order === null) {
            throw new NotFoundHttpException('Order not found.');
        }

        $status = OrderStatusEnum::tryFrom($order->status);

        if ($status !== null && in_array($status, self::STOCK_RESERVING_STATUSES, true)) {
            $this->restoreStock($orderId);
        }

        $this->orderRepository->delete($orderId);
    }

    private function restoreStock(int $orderId): void
    {
        foreach ($this->orderItemRepository->getAllByOrderId($orderId) as $item) {
            $this->bookRepository->incrementStock($item->bookId, $item->quantity);
        }
    }
}
