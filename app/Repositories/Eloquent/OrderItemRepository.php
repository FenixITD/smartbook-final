<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\OrderItem\OrderItemDto;
use App\Dto\OrderItem\OrderItemFiltersDto;
use App\Dto\OrderItem\OrderItemResponseDto;
use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;

final class OrderItemRepository implements OrderItemRepositoryInterface
{
    /** @return array<OrderItemResponseDto> */
    public function getList(OrderItemFiltersDto $filters): array
    {
        return OrderItem::query()
            ->when($filters->id !== null, static fn ($q) => $q->where('id', $filters->id))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (OrderItem $item) => OrderItemResponseDto::fromModel($item))
            ->all();
    }

    public function getById(int $id): OrderItemResponseDto|null
    {
        $orderItem = OrderItem::find($id);

        return $orderItem !== null ? OrderItemResponseDto::fromModel($orderItem) : null;
    }

    public function create(OrderItemDto $data): OrderItemResponseDto
    {
        /** @var OrderItem $orderItem */
        $orderItem = OrderItem::create($data->toArray());

        return OrderItemResponseDto::fromModel($orderItem);
    }

    public function update(int $id, OrderItemDto $data): OrderItemResponseDto|null
    {
        $orderItem = OrderItem::findOrFail($id);

        $orderItem->update($data->toArray());

        /** @var OrderItem $fresh */
        $fresh = $orderItem->fresh();

        return OrderItemResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) OrderItem::destroy($id);
    }
}
