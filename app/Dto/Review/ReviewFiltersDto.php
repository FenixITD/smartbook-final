<?php

declare(strict_types=1);

namespace App\Dto\Review;

final readonly class ReviewFiltersDto
{
    public function __construct(
        public int|null $id = null,
        public string|null $search = null,
        public int $perPage = 15,
        public string $sortBy = 'id',
        public string $sortDirection = 'desc',
    ) {
    }
}
