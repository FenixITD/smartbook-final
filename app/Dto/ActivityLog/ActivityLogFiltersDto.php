<?php

declare(strict_types=1);

namespace App\Dto\ActivityLog;

final readonly class ActivityLogFiltersDto
{
    public function __construct(
        public int $perPage = 20,
        public string|null $logName = null,
        public string|null $subjectType = null,
    ) {
    }
}
