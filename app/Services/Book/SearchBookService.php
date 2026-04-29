<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class SearchBookService
{
    public function __construct(
        private BookRepositoryInterface $repository,
        private SearchBookByQueryService $searchService,
    ) {
    }

    /** @return array<BookResponseDto> */
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
