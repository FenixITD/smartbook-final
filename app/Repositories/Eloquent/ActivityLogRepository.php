<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use App\Services\Clickhouse\ClickhouseManagerService;

final readonly class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function __construct(private ClickhouseManagerService $clickhouse) {}

    public function getPaginated(ActivityLogFiltersDto $filters): PaginatedResponseDto
    {
        $query = $this->clickhouse->table('activity_log');

        if ($filters->logName !== null) {
            $query->where('log_name', $filters->logName);
        }

        if ($filters->causerId !== null) {
            $query->where('causer_id', $filters->causerId);
        }

        if ($filters->subjectType !== null) {
            $query->where('subject_type', $filters->subjectType);
        }

        if ($filters->logNames !== []) {
            $query->whereIn('log_name', $filters->logNames);
        }

        $total = $query->count();

        $rows = $query
            ->orderByDesc('created_at')
            ->limit($filters->perPage)
            ->offset(($filters->page - 1) * $filters->perPage)
            ->get();

        return PaginatedResponseDto::create(
            items: $rows,
            total: $total,
            perPage: $filters->perPage,
            currentPage: $filters->page,
        );
    }
}
