<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Order\OrderDTO;
use App\DTO\Order\OrderFiltersDTO;
use App\DTO\Order\OrderResponseDTO;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;

final class OrderRepository implements OrderRepositoryInterface
{
    public function getList(OrderFiltersDTO $filters): array
    {
        return Order::query()
            ->when($filters->search !== null, fn ($q) => $q->where('user_id', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(fn (Order $order) => OrderResponseDto::fromModel($order))
            ->all();
    }

    public function getById(int $id): ?OrderResponseDTO
    {
        $order = Order::find($id);

        return $order ? OrderResponseDTO::fromModel($order) : null;
    }

    public function create(OrderDTO $data): OrderResponseDTO
    {
        $order = Order::create($data->toArray());

        return OrderResponseDTO::fromModel($order);
    }

    public function update(Order $order, OrderDTO $data): ?OrderResponseDTO
    {
        $order->update($data->toArray());

        return OrderResponseDTO::fromModel($order->fresh());
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }
}
