<?php

declare(strict_types=1);

namespace App\Dto;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginatedResponseDto
{
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
    ) {
    }
}
