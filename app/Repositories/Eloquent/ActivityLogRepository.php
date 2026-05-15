<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Dto\ActivityLog\ActivityLogResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use Spatie\Activitylog\Models\Activity;

final class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function getPaginated(ActivityLogFiltersDto $filters): PaginatedResponseDto
    {
        $query = Activity::with('causer')->latest();

        if ($filters->logName !== null) {
            $query->where('log_name', $filters->logName);
        }

        if ($filters->subjectType !== null) {
            $query->where('subject_type', $filters->subjectType);
        }

        $paginator = $query->paginate($filters->perPage)->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(
                static fn (Activity $activity): ActivityLogResponseDto => ActivityLogResponseDto::fromModel($activity)
            )
        );

        return PaginatedResponseDto::fromPaginator($paginator);
    }
}
