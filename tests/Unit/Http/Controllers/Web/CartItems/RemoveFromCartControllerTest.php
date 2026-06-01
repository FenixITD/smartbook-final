<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\CartItems;

use App\Http\Controllers\Web\CartItems\RemoveFromCartController;
use App\Services\Cart\GuestCartService;
use App\Services\Cart\RemoveCartItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class RemoveFromCartControllerTest extends TestCase
{
    private MockInterface&RemoveCartItemService $authService;
    private MockInterface&GuestCartService $guestService;
    private RemoveFromCartController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = Mockery::mock(RemoveCartItemService::class);
        $this->guestService = Mockery::mock(GuestCartService::class);

        $this->app->instance(RemoveCartItemService::class, $this->authService);
        $this->app->instance(GuestCartService::class, $this->guestService);

        $this->controller = $this->app->make(RemoveFromCartController::class);

        // Инициализируем сессию для работы хелпера back()
        $this->app->make('session')->start();
    }

    public function test_auth_user_removes_item_via_auth_service(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $this->authService->shouldReceive('remove')->once()->with(10);
        $this->guestService->shouldNotReceive('remove');

        $response = ($this->controller)(10);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));
    }

    public function test_guest_user_removes_item_via_guest_service(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);

        $this->guestService->shouldReceive('remove')->once()->with(5);
        $this->authService->shouldNotReceive('remove');

        $response = ($this->controller)(5);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));
    }
}
