<?php declare(strict_types=1);

namespace Tests\Feature\Web\UserActivity;

use App\Dto\PaginatedResponseDto;
use App\Models\User;
use App\Services\User\UserActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class UserActivityWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_view_user_activity(): void
    {
        $response = $this->get('/user-activity');
        $response->assertRedirect();
    }

    public function test_authenticated_user_can_view_own_activity(): void
    {
        $user = User::factory()->create();

        $mock = Mockery::mock(UserActivityService::class);
        $mock->shouldReceive('fetchWithBooks')->withAnyArgs()->andReturn([PaginatedResponseDto::empty(15), []]);
        $this->app->instance(UserActivityService::class, $mock);

        $response = $this->actingAs($user)->get('/user-activity');

        $response->assertStatus(200)->assertViewIs('user-activity.index');
    }
}
