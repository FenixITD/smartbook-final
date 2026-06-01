<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Auth;

use App\Dto\Auth\RegisterDto;
use App\Dto\User\UserResponseDto;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class RegisterControllerTest extends TestCase
{
    private MockInterface&UserRepositoryInterface $repository;
    private RegisterController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(RegisterController::class);
    }

    public function test_show_redirects_to_dashboard_when_authenticated(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $response = $this->controller->show();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('dashboard'), $response->getTargetUrl());
    }

    public function test_show_returns_register_view_when_guest(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);

        $response = $this->controller->show();

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('pages.auth.register', $response->getName());
    }

    public function test_store_redirects_to_dashboard_after_registration(): void
    {
        $this->repository->shouldReceive('create')->once()->andReturn($this->makeUserResponseDto(1));
        Auth::shouldReceive('loginUsingId')->once()->with(1);

        $response = $this->controller->store($this->makeRequest([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('dashboard'), $response->getTargetUrl());
    }

    public function test_store_calls_create_on_repository(): void
    {
        $this->repository->shouldReceive('create')->once()->andReturn($this->makeUserResponseDto(2));
        Auth::shouldReceive('loginUsingId')->once()->with(2);

        $this->controller->store($this->makeRequest([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]));
    }

    public function test_store_passes_dto_from_request_to_repository(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (RegisterDto $dto) =>
                $dto->name === 'Jane Doe'
                && $dto->email === 'jane@example.com'
            ))
            ->andReturn($this->makeUserResponseDto(3));

        Auth::shouldReceive('loginUsingId')->once()->with(3);

        $this->controller->store($this->makeRequest([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]));
    }

    private function makeRequest(array $data): RegisterRequest
    {
        $request = RegisterRequest::createFrom(
            Request::create('/register', 'POST', $data)
        );

        $session = Mockery::mock(Store::class)->shouldIgnoreMissing();
        $session->shouldReceive('regenerate')->andReturn(true);
        $request->setLaravelSession($session);

        return $request;
    }

    private function makeUserResponseDto(int $id): UserResponseDto
    {
        return new UserResponseDto(
            id: $id,
            name: 'Test',
            email: 'test@example.com',
            role: 'user',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00'
        );
    }
}
