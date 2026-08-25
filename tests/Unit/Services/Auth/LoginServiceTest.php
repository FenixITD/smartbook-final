<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\Dto\Auth\LoginDto;
use App\Services\Auth\LoginService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginServiceTest extends TestCase
{
    private LoginService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LoginService();
    }

    private function makeDto(
        string $email = 'user@example.com',
        string $password = 'secret',
        bool $remember = false,
        string|null $ip = '127.0.0.1',
    ): LoginDto {
        return new LoginDto($email, $password, $remember, $ip);
    }

    public function test_returns_true_on_successful_login(): void
    {
        RateLimiter::shouldReceive('tooManyAttempts')->twice()->andReturn(false);
        Auth::shouldReceive('attempt')->once()->andReturn(true);
        RateLimiter::shouldReceive('clear')->once();

        $result = $this->service->login($this->makeDto());

        $this->assertTrue($result);
    }

    public function test_returns_false_when_credentials_are_wrong(): void
    {
        RateLimiter::shouldReceive('tooManyAttempts')->twice()->andReturn(false);
        Auth::shouldReceive('attempt')->once()->andReturn(false);
        RateLimiter::shouldReceive('hit')->twice();

        $result = $this->service->login($this->makeDto());

        $this->assertFalse($result);
    }

    public function test_hits_rate_limiter_on_failed_attempt(): void
    {
        RateLimiter::shouldReceive('tooManyAttempts')->twice()->andReturn(false);
        Auth::shouldReceive('attempt')->once()->andReturn(false);
        RateLimiter::shouldReceive('hit')->twice();

        $this->service->login($this->makeDto());
    }

    public function test_clears_rate_limiter_on_successful_login(): void
    {
        RateLimiter::shouldReceive('tooManyAttempts')->twice()->andReturn(false);
        Auth::shouldReceive('attempt')->once()->andReturn(true);
        RateLimiter::shouldReceive('clear')->once();

        $this->service->login($this->makeDto());
    }

    public function test_throws_validation_exception_when_rate_limit_exceeded(): void
    {
        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(true);
        RateLimiter::shouldReceive('availableIn')->once()->andReturn(30);

        $this->expectException(ValidationException::class);

        $this->service->login($this->makeDto());
    }

    public function test_validation_exception_contains_email_key(): void
    {
        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(true);
        RateLimiter::shouldReceive('availableIn')->once()->andReturn(60);

        try {
            $this->service->login($this->makeDto());
            $this->fail('ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }
    }

    public function test_does_not_attempt_auth_when_rate_limit_exceeded(): void
    {
        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(true);
        RateLimiter::shouldReceive('availableIn')->once()->andReturn(10);
        Auth::shouldReceive('attempt')->never();

        try {
            $this->service->login($this->makeDto());
        } catch (ValidationException) {
        }
    }

    public function test_passes_remember_flag_to_auth(): void
    {
        RateLimiter::shouldReceive('tooManyAttempts')->twice()->andReturn(false);
        Auth::shouldReceive('attempt')
            ->once()
            ->withArgs(function (array $credentials, bool $remember): bool {
                return $credentials === ['email' => 'user@example.com', 'password' => 'secret']
                    && $remember === true;
            })
            ->andReturn(true);
        RateLimiter::shouldReceive('clear')->once();

        $result = $this->service->login($this->makeDto(remember: true));

        $this->assertTrue($result);
    }

    public function test_throttle_key_includes_email_and_ip(): void
    {
        RateLimiter::shouldReceive('tooManyAttempts')
            ->twice()
            ->withArgs(function (string $key): bool {
                return str_contains($key, 'user@example.com') && str_contains($key, '192.168.1.1');
            })
            ->andReturn(false);

        Auth::shouldReceive('attempt')->once()->andReturn(true);
        RateLimiter::shouldReceive('clear')->once();

        $this->service->login($this->makeDto(ip: '192.168.1.1'));
    }

    public function test_works_with_null_ip(): void
    {
        RateLimiter::shouldReceive('tooManyAttempts')->twice()->andReturn(false);
        Auth::shouldReceive('attempt')->once()->andReturn(true);
        RateLimiter::shouldReceive('clear')->once();

        $result = $this->service->login($this->makeDto(ip: null));

        $this->assertTrue($result);
    }
}
