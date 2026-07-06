<?php

declare(strict_types=1);

namespace App\Traits;

use App\Dto\PaginatedResponseDto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

trait CreatesPaginatedResponse
{
    /**
     * @param iterable<mixed> $items
     */
    protected function createPaginatedResponse(iterable $items, int $total, int $perPage): PaginatedResponseDto
    {
        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            Paginator::resolveCurrentPage() ?: 1,
            ['path' => Paginator::resolveCurrentPath()]
        );

        $paginator->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }
}
