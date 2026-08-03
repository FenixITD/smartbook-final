<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\OrderItems;

use App\Dto\OrderItem\OrderItemDto;
use App\Dto\OrderItem\OrderItemResponseDto;
use App\Http\Controllers\Api\OrderItems\UpdateOrderItemController;
use App\Http\Requests\OrderItem\OrderItemDataRequest;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateOrderItemControllerTest extends TestCase
{
    private MockInterface&OrderItemRepositoryInterface $repository;
    private UpdateOrderItemController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(OrderItemRepositoryInterface::class);
        $this->app->instance(OrderItemRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(UpdateOrderItemController::class);
    }

    public function test_returns_200_with_updated_order_item(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(4, Mockery::type(OrderItemDto::class))
            ->andReturn($this->makeResponseDto(id: 4, orderId: 1, bookId: 2, quantity: 3, priceAtPurchase: '14.99'));

        $response = ($this->controller)($this->makeRequest([
            'orderId' => 1,
            'bookId' => 2,
            'quantity' => 3,
            'priceAtPurchase' => 14.99,
        ]), 4);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_updated_order_item_data(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->andReturn($this->makeResponseDto(id: 4, orderId: 1, bookId: 2, quantity: 3, priceAtPurchase: '14.99'));

        $response = ($this->controller)($this->makeRequest([
            'orderId' => 1,
            'bookId' => 2,
            'quantity' => 3,
            'priceAtPurchase' => 14.99,
        ]), 4);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(4, $data['id']);
        $this->assertSame(1, $data['orderId']);
        $this->assertSame(2, $data['bookId']);
        $this->assertSame(3, $data['quantity']);
        $this->assertSame('14.99', $data['priceAtPurchase']);
    }

    public function test_passes_correct_id_and_dto_to_repository(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(
                7,
                Mockery::on(fn (OrderItemDto $arg) =>
                    $arg->orderId === 2
                    && $arg->bookId === 5
                    && $arg->quantity === 1
                    && $arg->priceAtPurchase === '29.99'
                ),
            )
            ->andReturn($this->makeResponseDto(id: 7, orderId: 2, bookId: 5, quantity: 1, priceAtPurchase: '29.99'));

        ($this->controller)($this->makeRequest([
            'orderId' => 2,
            'bookId' => 5,
            'quantity' => 1,
            'priceAtPurchase' => 29.99,
        ]), 7);
    }

    private function makeRequest(array $data): OrderItemDataRequest
    {
        return OrderItemDataRequest::createFrom(
            Request::create('/api/orderItems/1', 'PUT', $data)
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
