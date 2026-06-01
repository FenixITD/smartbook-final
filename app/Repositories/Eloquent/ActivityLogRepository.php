<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Models\ActivityLog;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;

final readonly class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function getPaginated(ActivityLogFiltersDto $filters): PaginatedResponseDto
    {
        $query = ActivityLog::query();

        // Use getQuery() to bypass Eloquent's strict column checks from docblocks
        if ($filters->logName !== null) {
            $query->getQuery()->where('log_name', $filters->logName);
        }

        if ($filters->causerId !== null) {
            $query->getQuery()->where('causer_id', $filters->causerId);
        }

        if ($filters->subjectType !== null) {
            $query->getQuery()->where('subject_type', $filters->subjectType);
        }

        if ($filters->logNames !== []) {
            $query->getQuery()->whereIn('log_name', $filters->logNames);
        }

        $paginator = $query
            ->orderByDesc('created_at')
            ->paginate(
                perPage: $filters->perPage,
                page: $filters->page,
            );

        return PaginatedResponseDto::fromPaginator($paginator);
    }
}
