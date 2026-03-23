<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\OrderItem\OrderItemDto;
use App\DTO\OrderItem\OrderItemFiltersDto;
use App\DTO\OrderItem\OrderItemResponseDto;
use App\Models\OrderItem;

interface OrderItemRepositoryInterface
{
    /**
     * @return array<OrderItemResponseDto>
     */
    public function getList(OrderItemFiltersDto $filters): array;

    public function getById(int $id): ?OrderItemResponseDto;

    public function create(OrderItemDto $data): OrderItemResponseDto;

    public function update(OrderItem $orderItem, OrderItemDto $data): ?OrderItemResponseDto;

    public function delete(OrderItem $orderItem): bool;
}
