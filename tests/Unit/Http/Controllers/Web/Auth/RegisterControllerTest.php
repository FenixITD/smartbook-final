<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Auth;

use App\Dto\Auth\RegisterDto;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\LoginService;
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
    private MockInterface&LoginService $authService;
    private RegisterController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = Mockery::mock(LoginService::class);
        $this->app->instance(LoginService::class, $this->authService);
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
        $this->authService->shouldReceive('register')->once();

        $response = $this->controller->store($this->makeRequest([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('dashboard'), $response->getTargetUrl());
    }

    public function test_store_calls_register_on_service(): void
    {
        $this->authService
            ->shouldReceive('register')
            ->once();

        $this->controller->store($this->makeRequest([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]));
    }

    public function test_store_passes_dto_from_request_to_service(): void
    {
        $this->authService
            ->shouldReceive('register')
            ->once()
            ->with(Mockery::on(fn (RegisterDto $dto) =>
                $dto->name === 'Jane Doe'
                && $dto->email === 'jane@example.com'
            ));

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
}
