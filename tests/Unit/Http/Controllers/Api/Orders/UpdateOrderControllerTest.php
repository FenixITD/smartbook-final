<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Orders;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderResponseDto;
use App\Http\Controllers\Api\Orders\UpdateOrderController;
use App\Http\Requests\Order\OrderDataRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateOrderControllerTest extends TestCase
{
    private MockInterface&OrderRepositoryInterface $repository;
    private UpdateOrderController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(OrderRepositoryInterface::class);
        $this->app->instance(OrderRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(UpdateOrderController::class);
    }

    public function test_returns_200_with_updated_order(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(4, Mockery::type(OrderDto::class))
            ->andReturn($this->makeResponseDto(id: 4, status: 'shipped'));

        $response = ($this->controller)($this->makeRequest(['status' => 'shipped']), 4);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_updated_order_data(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->andReturn($this->makeResponseDto(id: 4, status: 'delivered'));

        $response = ($this->controller)($this->makeRequest(['status' => 'delivered']), 4);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(4, $data['id']);
        $this->assertSame('delivered', $data['status']);
        $this->assertSame(99.99, $data['total']);
        $this->assertSame('Pushkina 19', $data['shippingAddress']);
    }

    public function test_passes_correct_id_and_dto_to_repository(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(
                7,
                Mockery::on(fn (OrderDto $arg) => $arg->status === 'cancelled' && $arg->userId === 5),
            )
            ->andReturn($this->makeResponseDto(id: 7, status: 'cancelled'));

        ($this->controller)($this->makeRequest(['status' => 'cancelled', 'userId' => 5]), 7);
    }

    private function makeRequest(array $data = []): OrderDataRequest
    {
        return OrderDataRequest::createFrom(
            Request::create('/api/orders/1', 'PUT', array_merge([
                'userId' => 3,
                'total' => 99.99,
                'status' => 'pending',
                'shippingAddress' => 'Pushkina 19',
                'paymentMethod' => 'cash',
            ], $data))
        );
    }

    private function makeResponseDto(int $id, string $status = 'pending'): OrderResponseDto
    {
        return new OrderResponseDto(
            id: $id,
            userId: 3,
            userName: 'John Doe',
            total: 99.99,
            status: $status,
            shippingAddress: 'Pushkina 19',
            paymentMethod: 'cash',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
