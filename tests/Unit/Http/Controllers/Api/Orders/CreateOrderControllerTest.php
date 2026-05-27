<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Orders;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderResponseDto;
use App\Http\Controllers\Api\Orders\CreateOrderController;
use App\Http\Requests\Order\OrderDataRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateOrderControllerTest extends TestCase
{
    private MockInterface&OrderRepositoryInterface $repository;
    private CreateOrderController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(OrderRepositoryInterface::class);
        $this->app->instance(OrderRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(CreateOrderController::class);
    }

    public function test_returns_201_with_created_order(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->makeResponseDto(id: 1));

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_response_contains_created_order_data(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->andReturn($this->makeResponseDto(id: 5));

        $response = ($this->controller)($this->makeRequest());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(5, $data['id']);
        $this->assertSame(3, $data['userId']);
        $this->assertSame(99.99, $data['total']);
        $this->assertSame('pending', $data['status']);
        $this->assertSame('Pushkina 19', $data['shippingAddress']);
        $this->assertSame('cash', $data['paymentMethod']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_passes_dto_from_request_to_repository(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (OrderDto $arg) => $arg->userId === 7 && $arg->status === 'shipped'))
            ->andReturn($this->makeResponseDto(id: 2));

        ($this->controller)($this->makeRequest(['userId' => 7, 'status' => 'shipped']));
    }

    private function makeRequest(array $data = []): OrderDataRequest
    {
        return OrderDataRequest::createFrom(
            Request::create('/api/orders', 'POST', array_merge([
                'userId' => 3,
                'total' => 99.99,
                'status' => 'pending',
                'shippingAddress' => 'Pushkina 19',
                'paymentMethod' => 'cash',
            ], $data))
        );
    }

    private function makeResponseDto(int $id): OrderResponseDto
    {
        return new OrderResponseDto(
            id: $id,
            userId: 3,
            userName: 'John Doe',
            total: 99.99,
            status: 'pending',
            shippingAddress: 'Pushkina 19',
            paymentMethod: 'cash',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
