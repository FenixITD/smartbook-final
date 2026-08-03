<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\CartItems;

use App\Http\Controllers\Web\CartItems\ShowCartController;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Cart\GuestCartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class ShowCartControllerTest extends TestCase
{
    private MockInterface&CartItemRepositoryInterface $repository;
    private MockInterface&GuestCartService $guestCartService;
    private ShowCartController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->guestCartService = Mockery::mock(GuestCartService::class);

        $this->app->instance(CartItemRepositoryInterface::class, $this->repository);
        $this->app->instance(GuestCartService::class, $this->guestCartService);

        $this->controller = $this->app->make(ShowCartController::class);
    }

    public function test_auth_user_sees_cart_from_repository(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('id')->twice()->andReturn(5);

        $this->repository->shouldReceive('getAllByUserId')->once()->with(5)->andReturn([]);
        $this->repository->shouldReceive('getTotalByUserId')->once()->with(5)->andReturn(150.50);

        $this->guestCartService->shouldNotReceive('getItems');
        $this->guestCartService->shouldNotReceive('getTotal');

        $response = ($this->controller)();

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('cart.index', $response->name());

        $data = $response->getData();
        $this->assertArrayHasKey('cartItems', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertSame([], $data['cartItems']);
        $this->assertSame('150.5', $data['total']);
    }

    public function test_guest_user_sees_cart_from_guest_service(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);

        $this->guestCartService->shouldReceive('getItems')->once()->andReturn([]);
        $this->guestCartService->shouldReceive('getTotal')->once()->andReturn(99.99);

        $this->repository->shouldNotReceive('getAllByUserId');
        $this->repository->shouldNotReceive('getTotalByUserId');

        $response = ($this->controller)();

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('cart.index', $response->name());

        $data = $response->getData();
        $this->assertSame([], $data['cartItems']);
        $this->assertSame('99.99', $data['total']);
    }
}
