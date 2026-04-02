<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Dashboard\DashboardFiltersDto;
use App\Dto\PaginatedResponseDto;

interface DashboardRepositoryInterface
{
    public function getDashboardList(DashboardFiltersDto $filters): PaginatedResponseDto;
}
