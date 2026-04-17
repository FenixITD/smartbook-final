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

    public function create(OrderItemDto $data): OrderItemResponseDto;

    public function update(int $id, OrderItemDto $data): OrderItemResponseDto|null;

    public function delete(int $id): bool;
}
