<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Dto\Order\OrderResponseDto;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class SearchSuggestOrderService
{
    public function __construct(
        private OrderRepositoryInterface $repository,
        private SearchOrderByQueryService $searchService,
    ) {
    }

    /**
     * @param string $query
     * @return array<int, array{id: int, user_name: string, status: string, url: string}>
     *
     * Fetches up to 5 order suggestions for autocomplete search, returning basic order details and their URL.
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
