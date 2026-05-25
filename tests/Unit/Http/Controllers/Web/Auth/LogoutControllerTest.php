<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Auth;

use App\Http\Controllers\Web\Auth\LogoutController;
use App\Http\Requests\Auth\LogoutRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class LogoutControllerTest extends TestCase
{
    private MockInterface&AuthService $authService;
    private LogoutController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = Mockery::mock(AuthService::class);
        $this->app->instance(AuthService::class, $this->authService);
        $this->controller = $this->app->make(LogoutController::class);
    }

    public function test_redirects_to_dashboard_after_logout(): void
    {
        $this->authService->shouldReceive('logout')->once();

        $response = ($this->controller)($this->makeRequest());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('dashboard'), $response->getTargetUrl());
    }

    public function test_calls_logout_on_auth_service(): void
    {
        $this->authService
            ->shouldReceive('logout')
            ->once();

        ($this->controller)($this->makeRequest());
    }

    public function test_invalidates_session(): void
    {
        $this->authService->shouldReceive('logout')->once();

        $session = Mockery::mock(Store::class)->shouldIgnoreMissing();
        $session->shouldReceive('invalidate')->once()->andReturn(true);
        $session->shouldReceive('regenerateToken')->once()->andReturnNull();

        $request = LogoutRequest::createFrom(Request::create('/logout', 'POST'));
        $request->setLaravelSession($session);

        ($this->controller)($request);
    }

    public function test_regenerates_session_token(): void
    {
        $this->authService->shouldReceive('logout')->once();

        $session = Mockery::mock(Store::class)->shouldIgnoreMissing();
        $session->shouldReceive('invalidate')->once()->andReturn(true);
        $session->shouldReceive('regenerateToken')->once()->andReturnNull();

        $request = LogoutRequest::createFrom(Request::create('/logout', 'POST'));
        $request->setLaravelSession($session);

        ($this->controller)($request);
    }

    private function makeRequest(): LogoutRequest
    {
        $request = LogoutRequest::createFrom(Request::create('/logout', 'POST'));

        $session = Mockery::mock(Store::class)->shouldIgnoreMissing();
        $session->shouldReceive('invalidate')->andReturn(true);
        $session->shouldReceive('regenerateToken')->andReturnNull();
        $request->setLaravelSession($session);

        return $request;
    }
}
