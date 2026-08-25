<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Clickhouse;

use App\Models\ClickhouseActivity;
use App\Models\User;
use App\Services\Clickhouse\ClickhouseActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ClickhouseActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClickhouseActivityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClickhouseActivityService();
    }

    public function test_build_row_with_minimal_data(): void
    {
        $activity = new ClickhouseActivity();

        $row = $this->service->buildRow($activity);

        $this->assertArrayHasKey('id', $row);
        $this->assertSame('default', $row['log_name']);
        $this->assertSame('', $row['description']);
        $this->assertSame('', $row['subject_type']);
        $this->assertNull($row['subject_id']);
        $this->assertSame('', $row['causer_type']);
        $this->assertNull($row['causer_id']);
        $this->assertSame('', $row['causer_name']);
        $this->assertSame('[]', $row['properties']);
        $this->assertArrayHasKey('created_at', $row);
        $this->assertArrayHasKey('updated_at', $row);
    }

    public function test_build_row_with_full_data(): void
    {
        $user = clone new User(['name' => 'John Doe']);
        $user->id = 1;

        $activity = new ClickhouseActivity();
        $activity->log_name = 'test_log';
        $activity->description = 'test_desc';
        $activity->subject_type = 'test_type';
        $activity->subject_id = 5;
        $activity->causer_type = 'user_type';
        $activity->causer_id = 1;
        $activity->setRelation('causer', $user);
        $activity->properties = new Collection(['key' => 'value']);

        $row = $this->service->buildRow($activity);

        $this->assertSame('test_log', $row['log_name']);
        $this->assertSame('test_desc', $row['description']);
        $this->assertSame('test_type', $row['subject_type']);
        $this->assertSame(5, $row['subject_id']);
        $this->assertSame('user_type', $row['causer_type']);
        $this->assertSame(1, $row['causer_id']);
        $this->assertSame('John Doe', $row['causer_name']);
        $this->assertSame('{"key":"value"}', $row['properties']);
    }

    public function test_resolves_causer_name_from_loaded_relation(): void
    {
        $user = new User();
        $user->name = 'Jane Doe';

        $activity = new ClickhouseActivity();
        $activity->causer_id = $user->id;
        $activity->setRelation('causer', $user);

        $row = $this->service->buildRow($activity);

        $this->assertSame('Jane Doe', $row['causer_name']);
    }
}
