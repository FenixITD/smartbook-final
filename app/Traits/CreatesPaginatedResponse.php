<?php

declare(strict_types=1);

namespace App\Traits;

use App\Dto\PaginatedResponseDto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

trait CreatesPaginatedResponse
{
    /**
     * @template T
     * @param array<array-key, T>|Collection<array-key, T> $items
     * @param callable|null $mapper
     */
    protected function createPaginatedResponse(array|Collection $items, int $total, int $perPage, ?callable $mapper = null): PaginatedResponseDto
    {
        $currentPage = max(1, Paginator::resolveCurrentPage());

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        $paginator->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator, $mapper);
    }
}
