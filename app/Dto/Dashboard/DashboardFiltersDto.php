<?php

declare(strict_types=1);

namespace App\Dto\Dashboard;

final readonly class DashboardFiltersDto
{
    public function __construct(
        public string|null $search = null,
        public int|null $genre = null,
        public int|null $author = null,
        public int|null $year = null,
        public string|null $status = null,
        public string $sort = 'rating',
        public int $perPage = 18,
    ) {
    }
}
