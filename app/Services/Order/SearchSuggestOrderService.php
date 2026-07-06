<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Dto\Order\OrderResponseDto;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\Abstracts\AbstractSearchSuggestService;

class SearchSuggestOrderService extends AbstractSearchSuggestService
{
    public function __construct(
        OrderRepositoryInterface $repository,
        SearchOrderByQueryService $searchService,
    ) {
        parent::__construct($repository, $searchService);
    }

    /**
     * @param OrderResponseDto $entity
     * @return array{id: int, user_name: string, status: string, url: string}
     */
    protected function mapResult(mixed $entity): array
    {
        return [
            'id' => $entity->id,
            'user_name' => $entity->userName,
            'status' => $entity->status,
            'url' => route('orders.show', $entity->id),
        ];
    }
}
