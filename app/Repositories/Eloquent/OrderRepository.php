<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Order\OrderDto;
use App\DTO\Order\OrderFiltersDto;
use App\DTO\Order\OrderResponseDto;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;

final class OrderRepository implements OrderRepositoryInterface
{
    public function getList(OrderFiltersDto $filters): array
    {
        return Order::query()
            ->when($filters->search !== null, fn ($q) => $q->where('user_id', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(fn (Order $order) => OrderResponseDto::fromModel($order))
            ->all();
    }

    public function getById(int $id): ?OrderResponseDto
    {
        $order = Order::find($id);

        return $order ? OrderResponseDto::fromModel($order) : null;
    }

    public function create(OrderDto $data): OrderResponseDto
    {
        $order = Order::create($data->toArray());

        return OrderResponseDto::fromModel($order);
    }

    public function update(Order $order, OrderDto $data): ?OrderResponseDto
    {
        $order->update($data->toArray());

        return OrderResponseDto::fromModel($order->fresh());
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }
}
