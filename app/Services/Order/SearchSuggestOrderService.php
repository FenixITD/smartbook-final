<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Dto\Order\OrderResponseDto;
use App\Repositories\Interfaces\OrderRepositoryInterface;

final readonly class SearchSuggestOrderService
{
    public function __construct(
        private OrderRepositoryInterface $repository,
        private SearchOrderByQueryService $searchService,
    ) {
    }

    /**
     * @return array<int, array{id: int, status: string, url: string}>
     */
    public function execute(string $query): array
    {
        $ids = $this->searchService->search($query, limit: 5);

        if ($ids === []) {
            return [];
        }

        return array_values(array_map(
            static fn (OrderResponseDto $order): array => [
                'id' => $order->id,
                'user_name' => $order->userName,
                'status' => $order->status,
                'url' => route('orders.show', $order->id),
            ],
            $this->repository->getByIds($ids),
        ));
    }
}
