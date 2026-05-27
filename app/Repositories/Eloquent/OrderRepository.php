<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderFiltersDto;
use App\Dto\Order\OrderResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;

final class OrderRepository implements OrderRepositoryInterface
{
    /** @return array<OrderResponseDto> */
    public function getList(OrderFiltersDto $filters): array
    {
        return Order::query()
            ->when($filters->id !== null, static fn ($q) => $q->where('id', $filters->id))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Order $order) => OrderResponseDto::fromModel($order))
            ->all();
    }

    /** @param array<int> $ids */
    public function getWebListByIds(array $ids, OrderFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = Order::query()
            ->whereIn('id', $ids)
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    public function getById(int $id): OrderResponseDto|null
    {
        $orderId = Order::find($id);

        return $orderId !== null ? OrderResponseDto::fromModel($orderId) : null;
    }

    public function findByIdWithRelations(int $id): OrderResponseDto
    {
        $orderId = Order::with(['user', 'items'])
            ->findOrFail($id);

        return OrderResponseDto::fromModel($orderId);
    }

    public function suggest(string $query): array
    {
        return Order::where('id', 'like', "%{$query}%")
            ->orWhere('status', 'like', "%{$query}%")
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(static fn (Order $order) => OrderResponseDto::fromModel($order))
            ->all();
    }

    public function getByIds(array $ids): array
    {
        return Order::with('user')
            ->whereIn('id', $ids)
            ->get()
            ->map(static fn (Order $order) => OrderResponseDto::fromModel($order))
            ->all();
    }

    public function create(OrderDto $data): OrderResponseDto
    {
        /** @var Order $order */
        $order = Order::create($data->toArray());

        return OrderResponseDto::fromModel($order);
    }

    public function update(int $id, OrderDto $data): OrderResponseDto|null
    {
        $order = Order::findOrFail($id);

        $order->update($data->toArray());

        /** @var Order $fresh */
        $fresh = $order->fresh();

        return OrderResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) Order::findOrFail($id)->delete();
    }
}
