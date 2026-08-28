<?php

declare(strict_types=1);

namespace App\Dto\Favorite;

final readonly class FavoriteFiltersDto
{
    public function __construct(
        public ?int $id = null,
        public ?string $search = null,
        public int $perPage = 15,
        public string $sortBy = 'id',
        public string $sortDirection = 'asc',
        public bool $showNonActive = false,
    ) {}
}
