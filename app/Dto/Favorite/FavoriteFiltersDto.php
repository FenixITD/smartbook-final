<?php

declare(strict_types=1);

namespace App\Dto\Favorite;

use App\Http\Requests\Favorite\FavoriteListRequest;

final readonly class FavoriteFiltersDto
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
        public string $sortBy = 'id',
        public string $sortDirection = 'asc',
    ) {}
}
