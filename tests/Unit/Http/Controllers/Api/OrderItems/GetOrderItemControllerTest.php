<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\OrderItems;

use App\Dto\OrderItem\OrderItemResponseDto;
use App\Http\Controllers\Api\OrderItems\GetOrderItemController;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetOrderItemControllerTest extends TestCase
{
    private MockInterface&OrderItemRepositoryInterface $repository;
    private GetOrderItemController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(OrderItemRepositoryInterface::class);
        $this->app->instance(OrderItemRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetOrderItemController::class);
    }

    public function test_returns_200_with_order_item(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(3)
            ->andReturn($this->makeResponseDto(id: 3, orderId: 1, bookId: 2, quantity: 1, priceAtPurchase: '12.50'));

        $response = ($this->controller)(3);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_correct_order_item_data(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->andReturn($this->makeResponseDto(id: 3, orderId: 1, bookId: 2, quantity: 1, priceAtPurchase: '12.50'));

        $response = ($this->controller)(3);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(3, $data['id']);
        $this->assertSame(1, $data['orderId']);
        $this->assertSame(2, $data['bookId']);
        $this->assertSame(1, $data['quantity']);
        $this->assertSame('12.50', $data['priceAtPurchase']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_calls_repository_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(42)
            ->andReturn($this->makeResponseDto(id: 42, orderId: 5, bookId: 7, quantity: 3, priceAtPurchase: '29.99'));

        ($this->controller)(42);
    }

    private function makeResponseDto(
        int $id,
        int $orderId,
        int $bookId,
        int $quantity,
        string $priceAtPurchase,
    ): OrderItemResponseDto {
        return new OrderItemResponseDto(
            id: $id,
            orderId: $orderId,
            bookId: $bookId,
            quantity: $quantity,
            priceAtPurchase: $priceAtPurchase,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
