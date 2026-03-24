<?php

declare(strict_types=1);

namespace App\Services\OrderItem;

use App\DTO\OrderItem\OrderItemDto;
use App\DTO\OrderItem\OrderItemResponseDto;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;

final readonly class CreateOrderItemService
{
    public function __construct(
        private OrderItemRepositoryInterface $repository
    ) {}

    public function execute(OrderItemDto $dto): OrderItemResponseDto
    {
        return $this->repository->create($dto);
    }
}
