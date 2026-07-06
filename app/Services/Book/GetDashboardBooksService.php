<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Dto\Dashboard\DashboardFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;

class GetDashboardBooksService
{
    public function __construct(
        private readonly SearchBookForDashboardService $searchService,
        private readonly BookRepositoryInterface $bookRepository,
    ) {
    }

    public function get(DashboardFiltersDto $filters): PaginatedResponseDto
    {
        [$ids, $total] = $this->searchService->search($filters);

        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        return $this->bookRepository->getDashboardListByIds($ids, $total, $filters);
    }
}
