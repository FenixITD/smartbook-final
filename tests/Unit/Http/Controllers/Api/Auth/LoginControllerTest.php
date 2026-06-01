<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Auth;

use App\Dto\Auth\LoginDto;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Requests\Auth\LoginRequest;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Auth\LoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class LoginControllerTest extends TestCase
{
    private MockInterface&LoginService $authService;
    private MockInterface&UserRepositoryInterface $repository;
    private LoginController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = Mockery::mock(LoginService::class);
        $this->repository = Mockery::mock(UserRepositoryInterface::class);

        $this->app->instance(LoginService::class, $this->authService);
        $this->app->instance(UserRepositoryInterface::class, $this->repository);

        $this->controller = $this->app->make(LoginController::class);
    }

    public function test_returns_200_with_token_on_valid_credentials(): void
    {
        $this->authService->shouldReceive('login')->once()->andReturn(true);

        Auth::shouldReceive('id')->once()->andReturn(1);

        $this->repository->shouldReceive('createToken')
            ->once()
            ->with(1, 'api-token')
            ->andReturn('plain-text-token');

        $response = ($this->controller)($this->makeRequest('user@example.com', 'secret'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_token_on_success(): void
    {
        $this->authService->shouldReceive('login')->andReturn(true);
        Auth::shouldReceive('id')->andReturn(1);
        $this->repository->shouldReceive('createToken')->andReturn('plain-text-token');

        $response = ($this->controller)($this->makeRequest('user@example.com', 'secret'));
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('token', $data);
        $this->assertSame('plain-text-token', $data['token']);
    }

    public function test_returns_401_when_credentials_are_invalid(): void
    {
        $this->authService->shouldReceive('login')->once()->andReturn(false);

        $response = ($this->controller)($this->makeRequest('user@example.com', 'wrong-password'));

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_response_contains_message_on_401(): void
    {
        $this->authService->shouldReceive('login')->andReturn(false);

        $response = ($this->controller)($this->makeRequest('user@example.com', 'wrong-password'));
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $data);
    }

    public function test_passes_dto_with_correct_email_and_password_to_service(): void
    {
        $this->authService
            ->shouldReceive('login')
            ->once()
            ->with(Mockery::on(function (LoginDto $dto) {
                return $dto->email === 'tolkien@example.com'
                    && $dto->password === 'my-password';
            }))
            ->andReturn(true);

        Auth::shouldReceive('id')->andReturn(1);
        $this->repository->shouldReceive('createToken')->andReturn('token');

        ($this->controller)($this->makeRequest('tolkien@example.com', 'my-password'));
    }

    private function makeRequest(string $email, string $password): LoginRequest
    {
        return LoginRequest::createFrom(
            Request::create('/api/login', 'POST', [
                'email' => $email,
                'password' => $password,
            ])
        );
    }
}
