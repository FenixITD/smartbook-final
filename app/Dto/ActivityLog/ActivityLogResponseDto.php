<?php

declare(strict_types=1);

namespace App\Dto\ActivityLog;

use Spatie\Activitylog\Models\Activity;

final readonly class ActivityLogResponseDto
{
    public static function fromModel(Activity $activity): self
    {
        return new self(
            id: $activity->id,
            logName: $activity->log_name,
            description: $activity->description,
            subjectType: $activity->subject_type !== null
                ? class_basename($activity->subject_type)
                : null,
            subjectId: $activity->subject_id,
            causerName: $activity->causer?->name,
            causerId: $activity->causer_id,
            properties: $activity->properties->toArray(),
            createdAt: $activity->created_at?->toDateTimeString() ?? '',
        );
    }

    public function __construct(
        public int $id,
        public string|null $logName,
        public string $description,
        public string|null $subjectType,
        public int|null $subjectId,
        public string|null $causerName,
        public int|null $causerId,
        public array $properties,
        public string $createdAt,
    ) {
    }
}
