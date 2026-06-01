<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Orders;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderResponseDto;
use App\Http\Controllers\Web\Orders\UpdateOrderController;
use App\Http\Requests\Order\OrderDataRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
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

    public function test_edit_returns_view_with_order_data(): void
    {
        $dto = $this->makeResponseDto(2);

        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with(2)
            ->andReturn($dto);

        $response = $this->controller->edit(2);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('orders.edit', $response->name());
        $this->assertArrayHasKey('order', $response->getData());
        $this->assertSame($dto, $response->getData()['order']);
    }

    public function test_update_calls_repository_update_and_redirects(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(
                4,
                Mockery::on(function (OrderDto $dto) {
                    return $dto->userId === 7
                        && $dto->total === 200.0
                        && $dto->status === 'shipped'
                        && $dto->shippingAddress === 'New Address'
                        && $dto->paymentMethod === 'card';
                })
            )
            ->andReturn($this->makeResponseDto(4));

        $response = $this->controller->update($this->makeRequest([
            'userId' => 7,
            'total' => 200.0,
            'status' => 'shipped',
            'shippingAddress' => 'New Address',
            'paymentMethod' => 'card',
        ]), 4);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('orders.index'), $response->getTargetUrl());
    }

    private function makeRequest(array $data): OrderDataRequest
    {
        return OrderDataRequest::createFrom(
            Request::create('/orders/1', 'PUT', $data)
        );
    }

    private function makeResponseDto(int $id): OrderResponseDto
    {
        return new OrderResponseDto(
            id: $id,
            userId: 7,
            userName: 'John Doe',
            total: 200.0,
            status: 'shipped',
            shippingAddress: 'New Address',
            paymentMethod: 'card',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
