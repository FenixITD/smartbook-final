<?php

declare(strict_types=1);

namespace App\Traits;

use App\Dto\PaginatedResponseDto;

trait HandlesWebListSearch
{
    protected function handleWebListSearch(
        mixed $filters,
        mixed $repository,
        mixed $searchService
    ): PaginatedResponseDto {
        if ($filters->search === null || $filters->search === '') {
            return $repository->getWebList($filters);
        }

        [$ids, $total] = $searchService->search($filters);

        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        return $repository->getWebListByIds($ids, $total, $filters);
    }
}
