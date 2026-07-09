<?php declare(strict_types=1);

namespace Tests\Feature\Web\ActivityLog;

use App\Dto\PaginatedResponseDto;
use App\Models\User;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class ActivityLogWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_activity_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $mock = Mockery::mock(ActivityLogRepositoryInterface::class);
        $mock->shouldReceive('getPaginated')->withAnyArgs()->andReturn(PaginatedResponseDto::empty(20));
        $this->app->instance(ActivityLogRepositoryInterface::class, $mock);

        $response = $this->actingAs($admin)->get('/activity-logs');

        $response->assertStatus(200)->assertViewIs('activity-logs.admin');
    }
}
