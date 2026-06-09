<?php

declare(strict_types=1);

namespace App\Dto\ActivityLog;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;

final readonly class ActivityLogResponseDto
{
    /**
     * @param array<string, mixed> $properties
     */
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
        /** @var User|null $causer */
        $causer = $activity->causer;

        $props = $activity->properties;

        $propertiesArray = $props instanceof Collection ? $props->toArray() : [];

        /** @var array<string, mixed> $propertiesAssoc */
        $propertiesAssoc = $propertiesArray;

        return new self(
            id: $activity->id,
            logName: $activity->log_name,
            description: $activity->description,
            subjectType: $activity->subject_type,
            subjectId: $activity->subject_id,
            causerName: $causer !== null ? $causer->name : null,
            causerId: $activity->causer_id,
            properties: $propertiesAssoc,
            createdAt: $activity->created_at !== null ? $activity->created_at->toDateTimeString() : '',
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        $propertiesRaw = $row['properties'] ?? '{}';

        /** @var array<string, mixed> $properties */
        $properties = is_string($propertiesRaw)
            ? (json_decode($propertiesRaw, true) ?? [])
            : (array) $propertiesRaw;

        $id = $row['id'] ?? 0;
        $logName = $row['log_name'] ?? null;
        $description = $row['description'] ?? '';
        $subjectType = $row['subject_type'] ?? null;
        $subjectId = $row['subject_id'] ?? null;
        $causerName = $row['causer_name'] ?? null;
        $causerId = $row['causer_id'] ?? null;
        $createdAt = $row['created_at'] ?? '';

        return new self(
            id: is_numeric($id) ? (int) $id : 0,
            logName: is_string($logName) ? $logName : null,
            description: is_string($description) ? $description : '',
            subjectType: is_string($subjectType) ? $subjectType : null,
            subjectId: is_numeric($subjectId) ? (int) $subjectId : null,
            causerName: is_string($causerName) ? $causerName : null,
            causerId: is_numeric($causerId) ? (int) $causerId : null,
            properties: $properties,
            createdAt: is_string($createdAt) ? $createdAt : '',
        );
    }
}
