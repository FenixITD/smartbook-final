<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\DTO\Order\OrderDto;
use App\DTO\Order\OrderResponseDto;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;

final readonly class UpdateOrderService
{
    public function __construct(
        private OrderRepositoryInterface $repository
    ) {}

    public function execute(Order $order, OrderDto $dto): OrderResponseDto
    {
        $this->repository->update($order, $dto);

        return OrderResponseDto::fromModel($order->fresh());
    }
}
