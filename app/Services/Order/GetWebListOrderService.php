<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Dto\Order\OrderFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\OrderRepositoryInterface;

final class GetWebListOrderService
{
    public function __construct(
        private readonly SearchOrderService $searchService,
        private readonly OrderRepositoryInterface $repository,
    ) {
    }

    public function get(OrderFiltersDto $filters): PaginatedResponseDto
    {
        $ids = $this->searchService->search($filters);

        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        return $this->repository->getWebListByIds($ids, $filters);
    }
}
