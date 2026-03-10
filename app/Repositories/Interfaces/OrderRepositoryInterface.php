<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\DTO\Order\OrderFiltersDTO;
use App\DTO\Order\OrderResponseDTO;
use App\Models\Order;

interface OrderRepositoryInterface
{
    /**
     * @return array<OrderResponseDTO>
     */
    public function getList(OrderFiltersDTO $filters): array;

    public function getById(int $id): ?OrderResponseDTO;

    public function create(array $data): OrderResponseDTO;

    public function update(Order $order, array $data): ?OrderResponseDTO;

    public function delete(Order $order): bool;
}
