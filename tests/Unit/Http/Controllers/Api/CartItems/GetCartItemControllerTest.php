<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\CartItems;

use App\Dto\CartItem\CartItemResponseDto;
use App\Http\Controllers\Api\CartItems\GetCartItemController;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetCartItemControllerTest extends TestCase
{
    private MockInterface&CartItemRepositoryInterface $repository;
    private GetCartItemController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->app->instance(CartItemRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetCartItemController::class);
    }

    public function test_returns_200_with_cart_item(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(3)
            ->andReturn($this->makeResponseDto(id: 3, userId: 1, bookId: 5, quantity: 2));

        $response = ($this->controller)(3);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_correct_cart_item_data(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->andReturn($this->makeResponseDto(id: 3, userId: 1, bookId: 5, quantity: 2));

        $response = ($this->controller)(3);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(3, $data['id']);
        $this->assertSame(1, $data['userId']);
        $this->assertSame(5, $data['bookId']);
        $this->assertSame(2, $data['quantity']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_calls_repository_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(42)
            ->andReturn($this->makeResponseDto(id: 42, userId: 2, bookId: 7, quantity: 1));

        ($this->controller)(42);
    }

    private function makeResponseDto(int $id, int $userId, int $bookId, int $quantity): CartItemResponseDto
    {
        return new CartItemResponseDto(
            id: $id,
            userId: $userId,
            bookId: $bookId,
            quantity: $quantity,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
