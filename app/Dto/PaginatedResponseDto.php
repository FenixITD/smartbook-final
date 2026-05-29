<?php

declare(strict_types=1);

namespace App\Dto;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginatedResponseDto
{
    /**
     * @param array<mixed> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
        public int $lastPage,
        public string $links = '',
    ) {}

    /**
     * @template TModel
     *
     * @param LengthAwarePaginator<int, TModel> $paginator
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            items: $paginator->items(),
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            links: $paginator->links()->toHtml(),
        );
    }

    /**
     * Build from raw data for non-Eloquent sources (e.g. ClickHouse).
     * Generates pagination HTML links the same way Eloquent would.
     *
     * @param array<mixed> $items
     */
    public static function create(
        array $items,
        int $total,
        int $perPage,
        int $currentPage,
    ): self {
        $lastPage = (int) max(1, ceil($total / $perPage));

        $paginator = new LengthAwarePaginator(
            items: $items,
            total: $total,
            perPage: $perPage,
            currentPage: $currentPage,
        );

        return new self(
            items: $items,
            total: $total,
            perPage: $perPage,
            currentPage: $currentPage,
            lastPage: $lastPage,
            links: $paginator->withQueryString()->links()->toHtml(),
        );
    }

    public static function empty(int $perPage): self
    {
        return new self(
            items: [],
            total: 0,
            perPage: $perPage,
            currentPage: 1,
            lastPage: 1,
        );
    }
}
