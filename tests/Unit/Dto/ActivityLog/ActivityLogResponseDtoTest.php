<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\ActivityLog;

use App\Dto\ActivityLog\ActivityLogResponseDto;
use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

final class ActivityLogResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data(): void
    {
        $causer = new User();
        $causer->id = 42;
        $causer->name = 'John Doe';

        $activity = new Activity();
        $activity->id = 1;
        $activity->log_name = 'cart';
        $activity->description = 'Item added to cart';
        $activity->subject_type = 'App\Models\Cart';
        $activity->subject_id = 15;
        $activity->causer_id = 42;
        $activity->created_at = Carbon::parse('2026-06-01 12:00:00');

        $activity->properties = ['quantity' => 2, 'price' => 500];

        $activity->setRelation('causer', $causer);

        $dto = ActivityLogResponseDto::fromModel($activity);

        $this->assertSame(1, $dto->id);
        $this->assertSame('cart', $dto->logName);
        $this->assertSame('Item added to cart', $dto->description);
        $this->assertSame('App\Models\Cart', $dto->subjectType);
        $this->assertSame(15, $dto->subjectId);
        $this->assertSame('John Doe', $dto->causerName);
        $this->assertSame(42, $dto->causerId);
        $this->assertSame(['quantity' => 2, 'price' => 500], $dto->properties);
        $this->assertSame('2026-06-01 12:00:00', $dto->createdAt);
    }

    public function test_from_model_creates_dto_with_minimal_data_and_nulls(): void
    {
        $activity = new Activity();
        $activity->id = 2;
        $activity->log_name = null;
        $activity->description = 'System event';
        $activity->subject_type = null;
        $activity->subject_id = null;
        $activity->causer_id = null;
        $activity->created_at = null;

        $activity->mergeCasts(['properties' => 'array']);
        $activity->properties = null;

        $activity->setRelation('causer', null);

        $dto = ActivityLogResponseDto::fromModel($activity);

        $this->assertSame(2, $dto->id);
        $this->assertNull($dto->logName);
        $this->assertSame('System event', $dto->description);
        $this->assertNull($dto->subjectType);
        $this->assertNull($dto->subjectId);
        $this->assertNull($dto->causerName);
        $this->assertNull($dto->causerId);
        $this->assertSame([], $dto->properties);
        $this->assertSame('', $dto->createdAt);
    }
}
