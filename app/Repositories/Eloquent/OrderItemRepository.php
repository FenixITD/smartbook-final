<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTO\OrderItem\OrderItemFiltersDTO;
use App\DTO\OrderItem\OrderItemResponseDTO;
use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;

final class OrderItemRepository implements OrderItemRepositoryInterface
{
    public function getList(OrderItemFiltersDTO $filters): array
    {
        $query = OrderItem::query()
            ->when($filters->search !== null, fn ($q) => $q->where('id', 'like', "%{$filters->search}%"));

        $paginator = $query->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return $paginator->getCollection()
            ->map(fn (OrderItem $orderItem) => OrderItemResponseDTO::fromModel($orderItem))->all();
    }

    public function getById(int $id): ?OrderItemResponseDTO
    {
        $orderItem = OrderItem::find($id);

        return $orderItem ? OrderItemResponseDTO::fromModel($orderItem) : null;
    }

    public function create(array $data): OrderItemResponseDTO
    {
        $orderItem = OrderItem::create($data);

        return OrderItemResponseDTO::fromModel($orderItem);
    }

    public function update(OrderItem $orderItem, array $data): ?OrderItemResponseDTO
    {
        $orderItem->update($data);

        return OrderItemResponseDTO::fromModel($orderItem->fresh());
    }

    public function delete(OrderItem $orderItem): bool
    {
        return $orderItem->delete();
    }
}
