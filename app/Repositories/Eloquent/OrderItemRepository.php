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
        $orderItemId = OrderItem::find($id);

        return $orderItemId !== null ? OrderItemResponseDto::fromModel($orderItemId) : null;
    }

    public function create(OrderItemDto $data): OrderItemResponseDto
    {
        /** @var OrderItem $orderItem */
        $orderItem = OrderItem::create($data->toArray());

        return OrderItemResponseDto::fromModel($orderItem);
    }

    /** @param array<OrderItemDto> $data */
    public function createMany(array $data): void
    {
        if ($data === []) {
            return;
        }

        $now = now();

        OrderItem::query()->insert(array_map(static fn (OrderItemDto $dto): array => [
            ...$dto->toArray(),
            'created_at' => $now,
            'updated_at' => $now,
        ], $data));
    }

    public function update(int $id, OrderItemDto $data): OrderItemResponseDto
    {
        $orderItem = OrderItem::findOrFail($id);

        $orderItem->update($data->toArray());

        /** @var OrderItem $fresh */
        $fresh = $orderItem->fresh();

        return OrderItemResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) OrderItem::findOrFail($id)->delete();
    }
}
