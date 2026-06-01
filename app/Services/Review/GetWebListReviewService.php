<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Dto\PaginatedResponseDto;
use App\Dto\Review\ReviewFiltersDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

class GetWebListReviewService
{
    public function __construct(
        private readonly SearchReviewService $searchService,
        private readonly ReviewRepositoryInterface $repository,
    ) {
    }

    public function get(ReviewFiltersDto $filters): PaginatedResponseDto
    {
        $ids = $this->searchService->search($filters);

        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        return $this->repository->getWebListByIds($ids, $filters);
    }
}
