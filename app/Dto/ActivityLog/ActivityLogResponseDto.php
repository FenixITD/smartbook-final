<?php

declare(strict_types=1);

namespace App\Dto\ActivityLog;

use Spatie\Activitylog\Models\Activity;

final readonly class ActivityLogResponseDto
{
    public function __construct(
        public int $id,
        public ?string $logName,
        public string $description,
        public ?string $subjectType,
        public ?int $subjectId,
        public ?string $causerName,
        public ?int $causerId,
        public array $properties,
        public string $createdAt,
    ) {}

    public static function fromModel(Activity $activity): self
    {
        return new self(
            id: $activity->id,
            logName: $activity->log_name,
            description: $activity->description,
            subjectType: $activity->subject_type,
            subjectId: $activity->subject_id,
            causerName: $activity->causer?->name,
            causerId: $activity->causer_id,
            properties: $activity->properties->toArray(),
            createdAt: $activity->created_at->toDateTimeString(),
        );
    }

    /**
     * Build from a raw ClickHouse row.
     *
     * The causer name is stored denormalized at write time,
     * so no additional DB lookup is needed.
     *
     * @param array<string, mixed> $row
     */
    public static function fromClickhouseRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            logName: $row['log_name'] !== '' ? $row['log_name'] : null,
            description: $row['description'],
            subjectType: $row['subject_type'] !== '' ? $row['subject_type'] : null,
            subjectId: isset($row['subject_id']) ? (int) $row['subject_id'] : null,
            causerName: $row['causer_name'] !== '' ? $row['causer_name'] : null,
            causerId: isset($row['causer_id']) ? (int) $row['causer_id'] : null,
            properties: json_decode($row['properties'] ?: '{}', true, flags: JSON_THROW_ON_ERROR),
            createdAt: $row['created_at'],
        );
    }
}
