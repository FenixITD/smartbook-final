<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\DTO\Order\OrderDTO;
use App\DTO\Order\OrderResponseDTO;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;

final readonly class UpdateOrderService
{
    public function __construct(
        private OrderRepositoryInterface $repository
    ) {}

    public function execute(Order $order, OrderDTO $dto): OrderResponseDTO
    {
        $this->repository->update($order, $dto);

        return OrderResponseDTO::fromModel($order->fresh());
    }
}
