<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Pagination\Paginator;

class SearchBookService
{
    public function __construct(
        private BookRepositoryInterface $repository,
        private SearchBookByQueryService $searchService,
    ) {
    }

    /**
     * @return array<BookResponseDto>
     */
    public function fetchList(BookFiltersDto $filters): array
    {
        if ($filters->search === null) {
            return $this->repository->getList($filters);
        }

        $page = max(1, Paginator::resolveCurrentPage());
        [$ids] = $this->searchService->searchPaginated($filters->search, $filters->perPage, $page);

        if ($ids === []) {
            return [];
        }

        return $this->repository->getListByIds($ids, $filters);
    }

    public function fetchWebList(BookFiltersDto $filters): PaginatedResponseDto
    {
        if ($filters->search === null) {
            return $this->repository->getWebList($filters);
        }

        $page = max(1, Paginator::resolveCurrentPage());
        [$ids, $total] = $this->searchService->searchPaginated($filters->search, $filters->perPage, $page);

        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        return $this->repository->getWebListByIds($ids, $total, $filters);
    }
}
