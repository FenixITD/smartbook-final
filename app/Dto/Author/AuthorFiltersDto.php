<?php

declare(strict_types=1);

namespace App\Dto\Author;

final readonly class AuthorFiltersDto
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
        public string $sortBy = 'id',
        public string $sortDirection = 'asc',
    ) {}
}
