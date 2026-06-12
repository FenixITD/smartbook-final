<?php

declare(strict_types=1);

namespace App\Services\Genre;

use App\Dto\Genre\GenreFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\GenreRepositoryInterface;

class GetWebListGenreService
{
    public function __construct(
        private readonly SearchGenreService $searchService,
        private readonly GenreRepositoryInterface $repository,
    ) {
    }

    public function get(GenreFiltersDto $filters): PaginatedResponseDto
    {
        if ($filters->search === null || $filters->search === '') {
            return $this->repository->getWebList($filters);
        }

        $ids = $this->searchService->search($filters);

        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        return $this->repository->getWebListByIds($ids, $filters);
    }
}
