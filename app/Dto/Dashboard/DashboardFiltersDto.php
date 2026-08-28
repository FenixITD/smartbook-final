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
        public string $sort = self::DEFAULT_SORT,
        public int $perPage = self::DEFAULT_PER_PAGE,
        public bool $showNonActive = false,
    ) {}

    public const DEFAULT_SORT = 'rating';

    public const DEFAULT_PER_PAGE = 18;
}
