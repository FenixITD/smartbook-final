<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Orders;

use App\Http\Controllers\Web\Orders\DeleteOrderController;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class DeleteOrderControllerTest extends TestCase
{
    private MockInterface&OrderRepositoryInterface $repository;
    private DeleteOrderController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(OrderRepositoryInterface::class);
        $this->app->instance(OrderRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(DeleteOrderController::class);
    }

    public function test_calls_repository_delete_and_redirects(): void
    {
        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with(10)
            ->andReturn(true);

        $response = ($this->controller)(10);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('orders.index'), $response->getTargetUrl());
    }
}
