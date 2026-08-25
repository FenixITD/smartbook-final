<?php declare(strict_types=1);

namespace Tests\Feature\Web\ActivityLog;

use App\Dto\ActivityLog\ActivityLogResponseDto;
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

        $logEntry = new ActivityLogResponseDto(
            id: 1,
            logName: 'default',
            description: 'User logged in',
            subjectType: 'App\\Models\\User',
            subjectId: 1,
            causerName: 'Admin',
            causerId: 1,
            properties: ['ip' => '127.0.0.1'],
            createdAt: now()->toDateTimeString(),
        );
        $paginated = new PaginatedResponseDto([$logEntry], 1, 20, 1, 1);

        $mock = Mockery::mock(ActivityLogRepositoryInterface::class);
        $mock->shouldReceive('getPaginated')
            ->once()
            ->andReturn($paginated);
        $this->app->instance(ActivityLogRepositoryInterface::class, $mock);

        $response = $this->actingAs($admin)->get('/activity-logs');

        $response->assertStatus(200)
            ->assertViewIs('activity-logs.admin')
            ->assertViewHas('logs', $paginated)
            ->assertViewHas('subjectTypes');
    }

    public function test_non_admin_cannot_view_activity_logs(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/activity-logs');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_view_activity_logs(): void
    {
        $response = $this->get('/activity-logs');

        $response->assertStatus(302);
    }
}
