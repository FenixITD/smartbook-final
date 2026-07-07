<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Dto\Order\OrderResponseDto;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\Abstracts\AbstractSearchSuggestService;

/**
 * @extends AbstractSearchSuggestService<OrderResponseDto>
 */
class SearchSuggestOrderService extends AbstractSearchSuggestService
{
    public function __construct(
        OrderRepositoryInterface $repository,
        SearchOrderByQueryService $searchService,
    ) {
        parent::__construct($repository, $searchService);
    }

    /**
     * @param mixed $entity
     * @return array<string, mixed>
     */
    protected function mapResult(mixed $entity): array
    {
        /** @var OrderResponseDto $entity */
        return [
            'id' => $entity->id,
            'user_name' => $entity->userName,
            'status' => $entity->status,
            'url' => route('orders.show', $entity->id),
        ];
    }
}
