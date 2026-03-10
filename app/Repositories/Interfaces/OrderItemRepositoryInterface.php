<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\DTO\OrderItem\OrderItemFiltersDTO;
use App\DTO\OrderItem\OrderItemResponseDTO;
use App\Models\OrderItem;

interface OrderItemRepositoryInterface
{
    /**
     * @return array<OrderItemResponseDTO>
     */
    public function getList(OrderItemFiltersDTO $filters): array;

    public function getById(int $id): ?OrderItemResponseDTO;

    public function create(array $data): OrderItemResponseDTO;

    public function update(OrderItem $orderItem, array $data): ?OrderItemResponseDTO;

    public function delete(OrderItem $orderItem): bool;
}
