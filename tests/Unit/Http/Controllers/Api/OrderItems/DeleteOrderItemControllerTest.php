<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\OrderItems;

use App\Http\Controllers\Api\OrderItems\DeleteOrderItemController;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class DeleteOrderItemControllerTest extends TestCase
{
    private MockInterface&OrderItemRepositoryInterface $repository;
    private DeleteOrderItemController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(OrderItemRepositoryInterface::class);
        $this->app->instance(OrderItemRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(DeleteOrderItemController::class);
    }

    public function test_returns_200_on_successful_delete(): void
    {
        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $response = ($this->controller)(1);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_success_message(): void
    {
        $this->repository
            ->shouldReceive('delete')
            ->andReturn(true);

        $response = ($this->controller)(1);
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $data);
        $this->assertSame('OrderItem deleted successfully', $data['message']);
    }

    public function test_calls_repository_delete_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with(42)
            ->andReturn(true);

        ($this->controller)(42);
    }

    public function test_returns_200_even_when_order_item_not_found(): void
    {
        // Repository returns false when record doesn't exist —
        // the controller itself doesn't differentiate, it always returns 200.
        $this->repository
            ->shouldReceive('delete')
            ->with(999)
            ->andReturn(false);

        $response = ($this->controller)(999);

        $this->assertSame(200, $response->getStatusCode());
    }
}
