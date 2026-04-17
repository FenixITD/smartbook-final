<?php

declare(strict_types=1);

namespace App\Dto;

use Illuminate\Pagination\LengthAwarePaginator;

final readonly class PaginatedResponseDto
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
