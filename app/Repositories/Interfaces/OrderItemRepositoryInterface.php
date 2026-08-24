<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\OrderItem\OrderItemDto;
use App\Dto\OrderItem\OrderItemFiltersDto;
use App\Dto\OrderItem\OrderItemResponseDto;

interface OrderItemRepositoryInterface
{
    /** @return array<OrderItemResponseDto> */
    public function getList(OrderItemFiltersDto $filters): array;

    public function getById(int $id): OrderItemResponseDto|null;

    /** @return array<OrderItemResponseDto> */
    public function getAllByOrderId(int $orderId): array;

    public function create(OrderItemDto $data): OrderItemResponseDto;

    /** @param array<OrderItemDto> $data */
    public function createMany(array $data): void;

    public function update(int $id, OrderItemDto $data): OrderItemResponseDto|null;

    public function delete(int $id): bool;
}
