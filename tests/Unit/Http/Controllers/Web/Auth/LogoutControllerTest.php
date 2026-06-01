<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Auth;

use App\Http\Controllers\Web\Auth\LogoutController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

final class LogoutControllerTest extends TestCase
{
    private LogoutController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = $this->app->make(LogoutController::class);
    }

    public function test_redirects_to_dashboard_after_logout(): void
    {
        Auth::shouldReceive('logout')->once();

        $response = ($this->controller)($this->makeRequest());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('dashboard'), $response->getTargetUrl());
    }

    public function test_calls_logout_on_auth_facade(): void
    {
        Auth::shouldReceive('logout')->once();

        ($this->controller)($this->makeRequest());
    }

    public function test_invalidates_session(): void
    {
        Auth::shouldReceive('logout')->once();

        $session = Mockery::mock(Store::class)->shouldIgnoreMissing();
        $session->shouldReceive('invalidate')->once()->andReturn(true);
        $session->shouldReceive('regenerateToken')->once()->andReturnNull();

        $request = Request::create('/logout', 'POST');
        $request->setLaravelSession($session);

        ($this->controller)($request);
    }

    public function test_regenerates_session_token(): void
    {
        Auth::shouldReceive('logout')->once();

        $session = Mockery::mock(Store::class)->shouldIgnoreMissing();
        $session->shouldReceive('invalidate')->once()->andReturn(true);
        $session->shouldReceive('regenerateToken')->once()->andReturnNull();

        $request = Request::create('/logout', 'POST');
        $request->setLaravelSession($session);

        ($this->controller)($request);
    }

    private function makeRequest(): Request
    {
        $request = Request::create('/logout', 'POST');

        $session = Mockery::mock(Store::class)->shouldIgnoreMissing();
        $session->shouldReceive('invalidate')->andReturn(true);
        $session->shouldReceive('regenerateToken')->andReturnNull();
        $request->setLaravelSession($session);

        return $request;
    }
}
