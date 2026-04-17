<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderFiltersDto;
use App\Dto\Order\OrderResponseDto;
use App\Dto\PaginatedResponseDto;

interface OrderRepositoryInterface
{
    /** @return array<OrderResponseDto> */
    public function getList(OrderFiltersDto $filters): array;

    public function getWebList(OrderFiltersDto $filters): PaginatedResponseDto;

    /** @return array<mixed> */
    public function all(): array;

    public function getById(int $id): OrderResponseDto|null;

    public function create(OrderDto $data): OrderResponseDto;

    public function update(int $id, OrderDto $data): OrderResponseDto|null;

    public function delete(int $id): bool;
}
