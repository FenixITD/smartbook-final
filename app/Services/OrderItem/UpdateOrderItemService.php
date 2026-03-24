<?php

declare(strict_types=1);

namespace App\Services\OrderItem;

use App\DTO\OrderItem\OrderItemDto;
use App\DTO\OrderItem\OrderItemResponseDto;
use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;

final readonly class UpdateOrderItemService
{
    public function __construct(
        private OrderItemRepositoryInterface $repository
    ) {}

    public function execute(OrderItem $orderItem, OrderItemDto $dto): OrderItemResponseDto
    {
        return $this->repository->update($orderItem, $dto);
    }
}
