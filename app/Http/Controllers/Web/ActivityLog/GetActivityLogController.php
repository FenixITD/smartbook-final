<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\ActivityLog;

use App\Http\Requests\ActivityLog\ActivityLogFilterRequest;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use Illuminate\View\View;

final readonly class GetActivityLogController
{
    public function __construct(
        private ActivityLogRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ActivityLogFilterRequest $request): View
    {
        $logs = $this->repository->getPaginated($request->toDto());

        $subjectTypes = array_keys(ActivityLogFilterRequest::SUBJECT_TYPE_MAP);

        return view('activity-logs.admin', compact('logs', 'subjectTypes'));
    }
}
