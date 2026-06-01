<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Orders;

use App\Dto\Order\OrderResponseDto;
use App\Http\Controllers\Web\Orders\GetByIdOrderController;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetByIdOrderControllerTest extends TestCase
{
    private MockInterface&OrderRepositoryInterface $repository;
    private GetByIdOrderController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(OrderRepositoryInterface::class);
        $this->app->instance(OrderRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetByIdOrderController::class);
    }

    public function test_returns_view_with_order_data(): void
    {
        $dto = $this->makeResponseDto(3);

        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with(3)
            ->andReturn($dto);

        $response = ($this->controller)(3);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('orders.show', $response->name());
        $this->assertArrayHasKey('order', $response->getData());
        $this->assertSame($dto, $response->getData()['order']);
    }

    private function makeResponseDto(int $id): OrderResponseDto
    {
        return new OrderResponseDto(
            id: $id,
            userId: 5,
            userName: 'John Doe',
            total: 100.0,
            status: 'pending',
            shippingAddress: 'Address',
            paymentMethod: 'cash',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
