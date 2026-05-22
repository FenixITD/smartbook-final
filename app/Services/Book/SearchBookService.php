<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;

class SearchBookService
{
    public function __construct(
        private BookRepositoryInterface $repository,
        private SearchBookByQueryService $searchService,
    ) {
    }

    /**
     * @param BookFiltersDto $filters
     * @return array<BookResponseDto>
     *
     * Retrieves an unpaginated list of books. Uses Elasticsearch if a search query is provided,
     * otherwise queries the repository directly.
     */
    public function getList(BookFiltersDto $filters): array
    {
        if ($filters->search === null) {
            return $this->repository->getList($filters);
        }

        $ids = $this->searchService->search($filters->search);

        if ($ids === []) {
            return [];
        }

        return $this->repository->getListByIds($ids, $filters);
    }

    /**
     * @param BookFiltersDto $filters
     * @return PaginatedResponseDto
     *
     * Retrieves a paginated list of books for the web interface, integrating with Elasticsearch for text search.
     */
    public function getWebList(BookFiltersDto $filters): PaginatedResponseDto
    {
        if ($filters->search === null) {
            return $this->repository->getWebList($filters);
        }

        $ids = $this->searchService->search($filters->search);

        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        return $this->repository->getWebListByIds($ids, $filters);
    }
}
