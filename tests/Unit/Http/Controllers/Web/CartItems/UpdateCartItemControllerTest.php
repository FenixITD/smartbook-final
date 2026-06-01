<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\CartItems;

use App\Http\Controllers\Web\CartItems\UpdateCartItemController;
use App\Http\Requests\CartItem\UpdateCartWebRequest;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Cart\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateCartItemControllerTest extends TestCase
{
    private MockInterface&CartItemRepositoryInterface $repository;
    private MockInterface&GuestCartService $guestCartService;
    private UpdateCartItemController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->guestCartService = Mockery::mock(GuestCartService::class);

        $this->app->instance(CartItemRepositoryInterface::class, $this->repository);
        $this->app->instance(GuestCartService::class, $this->guestCartService);

        $this->controller = $this->app->make(UpdateCartItemController::class);
    }

    public function test_auth_user_updates_item_via_repository(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('id')->once()->andReturn(7);

        $this->repository->shouldReceive('updateByUserAndBook')->once()->with(7, 3, 5);
        $this->guestCartService->shouldNotReceive('update');

        $response = ($this->controller)($this->makeRequest(['quantity' => 5]), 3);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));
    }

    public function test_guest_user_updates_item_via_guest_service(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);

        $this->guestCartService->shouldReceive('update')->once()->with(3, 10);
        $this->repository->shouldNotReceive('updateByUserAndBook');

        $response = ($this->controller)($this->makeRequest(['quantity' => 10]), 3);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));
    }

    private function makeRequest(array $data): UpdateCartWebRequest
    {
        $request = UpdateCartWebRequest::createFrom(
            Request::create('/cart/1', 'PUT', $data)
        );

        $session = $this->app->make('session.store');
        $request->setLaravelSession($session);

        return $request;
    }
}
