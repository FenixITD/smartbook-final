<?php

declare(strict_types=1);

namespace App\Traits;

use App\Dto\PaginatedResponseDto;

trait HandlesWebListSearch
{
    /**
     * @param mixed $filters
     * @param mixed $repository
     * @param mixed $searchService
     * @return PaginatedResponseDto
     */
    protected function handleWebListSearch(
        mixed $filters,
        mixed $repository,
        mixed $searchService
    ): PaginatedResponseDto {
        /** @var object{search: string|null, perPage: int} $filters */

        if ($filters->search === null || $filters->search === '') {
            /** @phpstan-ignore-next-line */
            return $repository->getWebList($filters);
        }

        /** @phpstan-ignore-next-line */
        $result = $searchService->search($filters);

        /** @var array{0: array<int>, 1: int} $result */
        $ids = $result[0];
        $total = $result[1];

        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        /** @phpstan-ignore-next-line */
        return $repository->getWebListByIds($ids, $total, $filters);
    }
}
