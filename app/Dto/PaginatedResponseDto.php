<?php

declare(strict_types=1);

namespace App\Dto;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class PaginatedResponseDto
{
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
        public int $lastPage,
        public string $links = '',
    ) {}

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
}
