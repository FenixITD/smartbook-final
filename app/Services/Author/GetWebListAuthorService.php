<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\Dto\Author\AuthorFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

final class GetWebListAuthorService
{
    public function __construct(
        private readonly SearchAuthorService $searchService,
        private readonly AuthorRepositoryInterface $repository,
    ) {}

    public function get(AuthorFiltersDto $filters): PaginatedResponseDto
    {
        $ids = $this->searchService->search($filters);

        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        return $this->repository->getWebListByIds($ids, $filters);
    }
}
