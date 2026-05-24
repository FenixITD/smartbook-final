<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Orders;

use App\Dto\Order\OrderResponseDto;
use App\Http\Controllers\Api\Orders\GetOrderController;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetOrderControllerTest extends TestCase
{
    private MockInterface&OrderRepositoryInterface $repository;
    private GetOrderController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(OrderRepositoryInterface::class);
        $this->app->instance(OrderRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetOrderController::class);
    }

    public function test_returns_200_with_order(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(3)
            ->andReturn($this->makeResponseDto(id: 3));

        $response = ($this->controller)(3);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_correct_order_data(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->andReturn($this->makeResponseDto(id: 3));

        $response = ($this->controller)(3);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(3, $data['id']);
        $this->assertSame(3, $data['userId']);
        $this->assertSame(99.99, $data['total']);
        $this->assertSame('pending', $data['status']);
        $this->assertSame('Pushkina 19', $data['shippingAddress']);
        $this->assertSame('cash', $data['paymentMethod']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_calls_repository_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(42)
            ->andReturn($this->makeResponseDto(id: 42));

        ($this->controller)(42);
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
