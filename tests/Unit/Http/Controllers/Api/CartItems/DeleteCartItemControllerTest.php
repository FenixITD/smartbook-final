<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\CartItems;

use App\Http\Controllers\Api\CartItems\DeleteCartItemController;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class DeleteCartItemControllerTest extends TestCase
{
    private MockInterface&CartItemRepositoryInterface $repository;
    private DeleteCartItemController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->app->instance(CartItemRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(DeleteCartItemController::class);
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
        $this->assertSame('CartItem deleted successfully', $data['message']);
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
}
