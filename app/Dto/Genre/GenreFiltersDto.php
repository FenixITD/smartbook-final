<?php

declare(strict_types=1);

namespace App\Dto\Genre;

final readonly class GenreFiltersDto
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
        public string $sortBy = 'id',
        public string $sortDirection = 'asc',
    ) {}
}
