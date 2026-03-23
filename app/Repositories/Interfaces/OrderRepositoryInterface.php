<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Order\OrderDto;
use App\DTO\Order\OrderFiltersDto;
use App\DTO\Order\OrderResponseDto;
use App\Models\Order;

interface OrderRepositoryInterface
{
    /**
     * @return array<OrderResponseDto>
     */
    public function getList(OrderFiltersDto $filters): array;

    public function getById(int $id): ?OrderResponseDto;

    public function create(OrderDto $data): OrderResponseDto;

    public function update(Order $order, OrderDto $data): ?OrderResponseDto;

    public function delete(Order $order): bool;
}
