<?php

declare(strict_types=1);

namespace App\Dto\Dashboard;

final readonly class DashboardFiltersDto
{
    public function __construct(
        public ?string $search = null,
        public ?int $genre = null,
        public ?int $author = null,
        public ?int $year = null,
        public ?string $status = null,
        public string $sort = 'rating',
        public int $perPage = 18,
    ) {}
}
