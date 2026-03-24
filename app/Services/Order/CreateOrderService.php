<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\DTO\Order\OrderDto;
use App\DTO\Order\OrderResponseDto;
use App\Repositories\Interfaces\OrderRepositoryInterface;

final readonly class CreateOrderService
{
    public function __construct(
        private OrderRepositoryInterface $repository
    ) {}

    public function execute(OrderDto $dto): OrderResponseDto
    {
        return $this->repository->create($dto);
    }
}
