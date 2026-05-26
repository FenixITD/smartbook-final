<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Auth;

use App\Dto\Auth\ApiLoginDto;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Requests\Auth\ApiLoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class LoginControllerTest extends TestCase
{
    private MockInterface $service;
    private LoginController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(LoginService::class);
        $this->app->instance(LoginService::class, $this->service);
        $this->controller = $this->app->make(LoginController::class);
    }

    public function test_returns_200_with_token_on_valid_credentials(): void
    {
        $this->service
            ->shouldReceive('apiLogin')
            ->once()
            ->andReturn('plain-text-token');

        $response = ($this->controller)($this->makeRequest('user@example.com', 'secret'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_token_on_success(): void
    {
        $this->service
            ->shouldReceive('apiLogin')
            ->andReturn('plain-text-token');

        $response = ($this->controller)($this->makeRequest('user@example.com', 'secret'));
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('token', $data);
        $this->assertSame('plain-text-token', $data['token']);
    }

    public function test_returns_401_when_credentials_are_invalid(): void
    {
        $this->service
            ->shouldReceive('apiLogin')
            ->once()
            ->andReturn(null);

        $response = ($this->controller)($this->makeRequest('user@example.com', 'wrong-password'));

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_response_contains_message_on_401(): void
    {
        $this->service
            ->shouldReceive('apiLogin')
            ->andReturn(null);

        $response = ($this->controller)($this->makeRequest('user@example.com', 'wrong-password'));
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $data);
    }

    public function test_passes_dto_with_correct_email_and_password_to_service(): void
    {
        $this->service
            ->shouldReceive('apiLogin')
            ->once()
            ->with(Mockery::on(function (ApiLoginDto $dto) {
                return $dto->email === 'tolkien@example.com'
                    && $dto->password === 'my-password';
            }))
            ->andReturn('token');

        ($this->controller)($this->makeRequest('tolkien@example.com', 'my-password'));
    }

    private function makeRequest(string $email, string $password): ApiLoginRequest
    {
        return ApiLoginRequest::createFrom(
            Request::create('/api/login', 'POST', [
                'email' => $email,
                'password' => $password,
            ])
        );
    }
}
