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
}
