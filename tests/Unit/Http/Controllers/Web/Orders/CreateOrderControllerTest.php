<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Orders;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderResponseDto;
use App\Http\Controllers\Web\Orders\CreateOrderController;
use App\Http\Requests\Order\OrderDataRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
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

    public function test_create_returns_view(): void
    {
        $response = $this->controller->create();

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('orders.create', $response->name());
    }

    public function test_store_calls_repository_create_and_redirects(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (OrderDto $dto) {
                return $dto->userId === 5
                    && $dto->total === 150.0
                    && $dto->status === 'pending'
                    && $dto->shippingAddress === '123 Main St'
                    && $dto->paymentMethod === 'card';
            }))
            ->andReturn($this->makeResponseDto(1));

        $response = $this->controller->store($this->makeRequest([
            'userId' => 5,
            'total' => 150.0,
            'status' => 'pending',
            'shippingAddress' => '123 Main St',
            'paymentMethod' => 'card',
        ]));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('orders.index'), $response->getTargetUrl());
    }

    private function makeRequest(array $data): OrderDataRequest
    {
        return OrderDataRequest::createFrom(
            Request::create('/orders', 'POST', $data)
        );
    }

    private function makeResponseDto(int $id): OrderResponseDto
    {
        return new OrderResponseDto(
            id: $id,
            userId: 5,
            userName: 'John Doe',
            total: 150.0,
            status: 'pending',
            shippingAddress: '123 Main St',
            paymentMethod: 'card',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
