<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Traits;

trait LogsApiActivity
{
    /** @param array<string, mixed> $properties */
    protected function logActivity(
        string $event,
        string $logName,
        int $subjectId,
        array $properties = [],
    ): void {
        activity($logName)
            ->causedBy(request()->user())
            ->withProperties(array_merge(['subject_id' => $subjectId], $properties))
            ->log($event);
    }
}
