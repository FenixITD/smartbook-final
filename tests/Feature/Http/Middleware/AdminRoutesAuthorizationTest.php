<?php declare(strict_types=1);
namespace Tests\Feature\Http\Middleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminRoutesAuthorizationTest extends TestCase {
    use RefreshDatabase;
    public function test_guests_are_redirected_or_receive_unauthorized(): void {
        $this->get('/orders')->assertRedirect('/login');
        $this->getJson('/api/orders')->assertUnauthorized();
    }
    public function test_regular_users_receive_forbidden_on_admin_routes(): void {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get('/orders')->assertForbidden();
        $this->actingAs($user)->getJson('/api/orders')->assertForbidden();
    }
    public function test_admins_can_access_admin_routes(): void {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/orders')->assertOk();
        $this->actingAs($admin)->getJson('/api/orders')->assertOk();
    }
}
