<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\CartItems;

use App\Http\Controllers\Web\CartItems\ClearCartController;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Cart\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class ClearCartControllerTest extends TestCase
{
    private MockInterface&CartItemRepositoryInterface $repository;
    private MockInterface&GuestCartService $guestCartService;
    private ClearCartController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->guestCartService = Mockery::mock(GuestCartService::class);

        $this->app->instance(CartItemRepositoryInterface::class, $this->repository);
        $this->app->instance(GuestCartService::class, $this->guestCartService);

        $this->controller = $this->app->make(ClearCartController::class);

        $this->app->make('session')->start();
    }

    public function test_auth_user_clears_cart_via_repository(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('id')->once()->andReturn(4);

        $this->repository->shouldReceive('deleteByUserId')->once()->with(4);
        $this->guestCartService->shouldNotReceive('clear');

        $response = ($this->controller)();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));
    }

    public function test_guest_user_clears_cart_via_guest_service(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);

        $this->guestCartService->shouldReceive('clear')->once();
        $this->repository->shouldNotReceive('deleteByUserId');

        $response = ($this->controller)();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));
    }
}
