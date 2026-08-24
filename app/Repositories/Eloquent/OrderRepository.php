<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderFiltersDto;
use App\Dto\Order\OrderResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Traits\OrdersByIds;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends AbstractEloquentRepository<Order, OrderResponseDto>
 */
final class OrderRepository extends AbstractEloquentRepository implements OrderRepositoryInterface
{
    use OrdersByIds;

    protected function getModelClass(): string
    {
        return Order::class;
    }

    protected function getResponseDtoClass(): string
    {
        return OrderResponseDto::class;
    }

    /** @return Builder<Order> */
    protected function query(): Builder
    {
        $query = parent::query();

        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->role !== 'admin') {
            $query->where('orders.user_id', $user->id);
        }

        return $query;
    }

    /** @return array<int, OrderResponseDto> */
    public function getList(OrderFiltersDto $filters): array
    {
        $query = $this->query()
            ->with('user:id,name')
            ->when($filters->id !== null, static fn ($q) => $q->where('id', $filters->id));

        if ($filters->sortBy === 'user_name') {
            $query->orderBy(
                User::select('name')->whereColumn('users.id', 'orders.user_id'),
                $filters->sortDirection
            );
        } else {
            $query->orderBy($filters->sortBy, $filters->sortDirection);
        }

        return $query->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Order $order) => OrderResponseDto::fromModel($order))
            ->all();
    }

    public function getWebList(OrderFiltersDto $filters): PaginatedResponseDto
    {
        $query = $this->query()->with('user:id,name');

        if ($filters->sortBy === 'user_name') {
            $query->orderBy(
                User::select('name')->whereColumn('users.id', 'orders.user_id'),
                $filters->sortDirection
            );
        } else {
            $query->orderBy($filters->sortBy, $filters->sortDirection);
        }

        $paginator = $query->paginate($filters->perPage)->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator, static fn (Order $order) => OrderResponseDto::fromModel($order));
    }

    public function getWebListByIds(array $ids, int $total, OrderFiltersDto $filters): PaginatedResponseDto
    {
        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        $items = $this->query()
            ->with('user:id,name')
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->get();

        return $this->createPaginatedResponse($items, $total, $filters->perPage, static fn (Order $order) => OrderResponseDto::fromModel($order));
    }

    public function getById(int $id): OrderResponseDto|null
    {
        /** @var Order|null $order */
        $order = $this->query()->with('user:id,name')->find($id);

        return $order !== null ? OrderResponseDto::fromModel($order) : null;
    }

    public function findByIdWithRelations(int $id): OrderResponseDto
    {
        /** @var Order $order */
        $order = $this->query()->with(['user', 'items'])->findOrFail($id);

        return OrderResponseDto::fromModel($order);
    }

    /**
     * @param array<int> $ids
     * @return array<int, OrderResponseDto>
     */
    public function getByIds(array $ids): array
    {
        return $this->query()->with('user:id,name')
            ->whereIn('id', $ids)
            ->get()
            ->map(static fn (Order $order) => OrderResponseDto::fromModel($order))
            ->all();
    }

    public function create(OrderDto $data): OrderResponseDto
    {
        /** @var Order $order */
        $order = $this->query()->create($data->toArray());
        $order->load('user:id,name');

        return OrderResponseDto::fromModel($order);
    }

    public function update(int $id, OrderDto $data): OrderResponseDto
    {
        /** @var Order $order */
        $order = $this->query()->findOrFail($id);

        $order->update($data->toArray());

        /** @var Order $fresh */
        $fresh = $order->fresh();
        $fresh->load('user:id,name');

        return OrderResponseDto::fromModel($fresh);
    }
}
