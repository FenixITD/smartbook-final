<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\OrderItem\OrderItemDto;
use App\DTO\OrderItem\OrderItemFiltersDto;
use App\DTO\OrderItem\OrderItemResponseDto;
use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;

final class OrderItemRepository implements OrderItemRepositoryInterface
{
    public function getList(OrderItemFiltersDto $filters): array
    {
        return OrderItem::query()
            ->when($filters->search !== null, fn ($q) => $q->where('id', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(fn (OrderItem $favorite) => OrderItemResponseDto::fromModel($favorite))
            ->all();
    }

    public function getById(int $id): ?OrderItemResponseDto
    {
        $orderItem = OrderItem::find($id);

        return $orderItem ? OrderItemResponseDto::fromModel($orderItem) : null;
    }

    public function create(OrderItemDto $data): OrderItemResponseDto
    {
        $orderItem = OrderItem::create($data->toArray());

        return OrderItemResponseDto::fromModel($orderItem);
    }

    public function update(OrderItem $orderItem, OrderItemDto $data): ?OrderItemResponseDto
    {
        $orderItem->update($data->toArray());

        return OrderItemResponseDto::fromModel($orderItem->fresh());
    }

    public function delete(OrderItem $orderItem): bool
    {
        return $orderItem->delete();
    }
}
