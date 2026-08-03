<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\OrderItems;

use App\Dto\OrderItem\OrderItemDto;
use App\Dto\OrderItem\OrderItemResponseDto;
use App\Http\Controllers\Api\OrderItems\CreateOrderItemController;
use App\Http\Requests\OrderItem\OrderItemDataRequest;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateOrderItemControllerTest extends TestCase
{
    private MockInterface&OrderItemRepositoryInterface $repository;
    private CreateOrderItemController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(OrderItemRepositoryInterface::class);
        $this->app->instance(OrderItemRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(CreateOrderItemController::class);
    }

    public function test_returns_201_with_created_order_item(): void
    {
        $responseDto = $this->makeResponseDto(id: 1, orderId: 2, bookId: 3, quantity: 2, priceAtPurchase: '16.99');

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturn($responseDto);

        $response = ($this->controller)($this->makeRequest([
            'orderId' => 2,
            'bookId' => 3,
            'quantity' => 2,
            'priceAtPurchase' => 16.99,
        ]));

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_response_contains_created_order_item_data(): void
    {
        $responseDto = $this->makeResponseDto(id: 5, orderId: 2, bookId: 3, quantity: 2, priceAtPurchase: '16.99');

        $this->repository
            ->shouldReceive('create')
            ->andReturn($responseDto);

        $response = ($this->controller)($this->makeRequest([
            'orderId' => 2,
            'bookId' => 3,
            'quantity' => 2,
            'priceAtPurchase' => 16.99,
        ]));
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(5, $data['id']);
        $this->assertSame(2, $data['orderId']);
        $this->assertSame(3, $data['bookId']);
        $this->assertSame(2, $data['quantity']);
        $this->assertSame('16.99', $data['priceAtPurchase']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_passes_dto_from_request_to_repository(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (OrderItemDto $arg) =>
                $arg->orderId === 2
                && $arg->bookId === 3
                && $arg->quantity === 4
                && $arg->priceAtPurchase === '9.99'
            ))
            ->andReturn($this->makeResponseDto(id: 1, orderId: 2, bookId: 3, quantity: 4, priceAtPurchase: '9.99'));

        ($this->controller)($this->makeRequest([
            'orderId' => 2,
            'bookId' => 3,
            'quantity' => 4,
            'priceAtPurchase' => 9.99,
        ]));
    }

    private function makeRequest(array $data): OrderItemDataRequest
    {
        return OrderItemDataRequest::createFrom(
            Request::create('/api/orderItems', 'POST', $data)
        );
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
