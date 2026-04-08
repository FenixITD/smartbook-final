<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\OrderItem\OrderItemDto;
use App\DTO\OrderItem\OrderItemFiltersDto;
use App\DTO\OrderItem\OrderItemResponseDto;

interface OrderItemRepositoryInterface
{
    /**
     * @return array<OrderItemResponseDto>
     */
    public function getList(OrderItemFiltersDto $filters): array;

    public function getById(int $id): ?OrderItemResponseDto;

    public function create(OrderItemDto $data): OrderItemResponseDto;

    public function update(int $id, OrderItemDto $data): ?OrderItemResponseDto;

    public function delete(int $id): bool;
}
