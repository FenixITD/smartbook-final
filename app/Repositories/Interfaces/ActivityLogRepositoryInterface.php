<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Dto\PaginatedResponseDto;

interface ActivityLogRepositoryInterface
{
    public function getPaginated(ActivityLogFiltersDto $filters): PaginatedResponseDto;
}
