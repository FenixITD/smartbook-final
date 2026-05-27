<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Auth;

use App\Dto\Auth\LoginDto;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class LoginControllerTest extends TestCase
{
    private MockInterface&LoginService $authService;
    private LoginController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = Mockery::mock(LoginService::class);
        $this->app->instance(LoginService::class, $this->authService);
        $this->controller = $this->app->make(LoginController::class);
    }

    public function test_show_redirects_to_dashboard_when_authenticated(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $response = $this->controller->show();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('dashboard'), $response->getTargetUrl());
    }

    public function test_show_returns_login_view_when_guest(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);

        $response = $this->controller->show();

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('pages.auth.login', $response->getName());
    }

    public function test_store_redirects_back_when_login_fails(): void
    {
        $this->authService
            ->shouldReceive('login')
            ->once()
            ->andReturn(false);

        $response = $this->controller->store($this->makeRequest([
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
    }

    public function test_store_redirects_to_dashboard_when_login_succeeds(): void
    {
        $this->authService
            ->shouldReceive('login')
            ->once()
            ->andReturn(true);

        $response = $this->controller->store($this->makeRequest([
            'email' => 'user@example.com',
            'password' => 'correct-password',
        ]));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('dashboard'), $response->getTargetUrl());
    }

    public function test_store_passes_dto_from_request_to_service(): void
    {
        $this->authService
            ->shouldReceive('login')
            ->once()
            ->with(Mockery::on(fn (LoginDto $dto) => $dto->email === 'user@example.com' && $dto->remember === true))
            ->andReturn(true);

        $this->controller->store($this->makeRequest([
            'email' => 'user@example.com',
            'password' => 'secret',
            'remember' => true,
        ]));
    }

    private function makeRequest(array $data): LoginRequest
    {
        $request = LoginRequest::createFrom(
            Request::create('/login', 'POST', $data)
        );

        $session = Mockery::mock(Store::class)->shouldIgnoreMissing();
        $session->shouldReceive('regenerate')->andReturn(true);
        $request->setLaravelSession($session);

        return $request;
    }
}
