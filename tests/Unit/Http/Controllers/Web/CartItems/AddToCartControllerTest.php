<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\CartItems;

use App\Http\Controllers\Web\CartItems\AddToCartController;
use App\Http\Requests\CartItem\AddToCartWebRequest;
use App\Services\Cart\AddCartItemService;
use App\Services\Cart\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class AddToCartControllerTest extends TestCase
{
    private MockInterface&AddCartItemService $authService;
    private MockInterface&GuestCartService $guestService;
    private AddToCartController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = Mockery::mock(AddCartItemService::class);
        $this->guestService = Mockery::mock(GuestCartService::class);

        $this->app->instance(AddCartItemService::class, $this->authService);
        $this->app->instance(GuestCartService::class, $this->guestService);

        $this->controller = $this->app->make(AddToCartController::class);
    }

    public function test_auth_user_adds_item_via_auth_service(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $this->authService->shouldReceive('add')->once()->with(10, 2);
        $this->guestService->shouldNotReceive('add');

        $response = ($this->controller)($this->makeRequest(['book_id' => 10, 'quantity' => 2]));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));
    }

    public function test_guest_user_adds_item_via_guest_service(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);

        $this->guestService->shouldReceive('add')->once()->with(5, 1);
        $this->authService->shouldNotReceive('add');

        $response = ($this->controller)($this->makeRequest(['book_id' => 5]));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));
    }

    private function makeRequest(array $data): AddToCartWebRequest
    {
        $request = AddToCartWebRequest::createFrom(
            Request::create('/cart', 'POST', $data)
        );

        $session = $this->app->make('session.store');
        $request->setLaravelSession($session);

        return $request;
    }
}
