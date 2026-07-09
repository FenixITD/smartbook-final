<?php declare(strict_types=1);

namespace Feature\Auth\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_api_login_fails_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_api_login_validates_request_data(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-an-email',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_api_login_throttles_after_too_many_attempts(): void
    {
        $email = 'throttle@example.com';
        $ip = '127.0.0.1';
        $throttleKey = Str::transliterate(Str::lower($email).'|'.$ip);

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($throttleKey);
        }

        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'password',
        ], ['REMOTE_ADDR' => $ip]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertStringContainsString('seconds', $response->json('errors.email.0'));
    }
}
