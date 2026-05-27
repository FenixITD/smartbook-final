<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Orders;

use App\Dto\Order\OrderFiltersDto;
use App\Dto\Order\OrderResponseDto;
use App\Http\Controllers\Api\Orders\GetListOrderController;
use App\Http\Requests\Order\OrderListRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListOrderControllerTest extends TestCase
{
    private MockInterface&OrderRepositoryInterface $repository;
    private GetListOrderController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(OrderRepositoryInterface::class);
        $this->app->instance(OrderRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetListOrderController::class);
    }

    public function test_returns_200_with_orders_list(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->andReturn([
                $this->makeResponseDto(1),
                $this->makeResponseDto(2),
            ]);

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_all_orders(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->andReturn([
                $this->makeResponseDto(1),
                $this->makeResponseDto(2),
                $this->makeResponseDto(3),
            ]);

        $response = ($this->controller)($this->makeRequest());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertCount(3, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame(2, $data[1]['id']);
        $this->assertSame(3, $data[2]['id']);
    }

    public function test_returns_empty_data_array_when_no_orders(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->andReturn([]);

        $response = ($this->controller)($this->makeRequest());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $data);
    }

    public function test_passes_filters_dto_from_request_to_repository(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->with(Mockery::on(function (OrderFiltersDto $arg) {
                return $arg->search === 'john'
                    && $arg->perPage === 10
                    && $arg->sortBy === 'status'
                    && $arg->sortDirection === 'desc';
            }))
            ->andReturn([]);

        ($this->controller)($this->makeRequest([
            'search' => 'john',
            'perPage' => 10,
            'sortBy' => 'status',
            'sortDirection' => 'desc',
        ]));
    }

    public function test_uses_default_filters_when_no_query_params(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->with(Mockery::on(function (OrderFiltersDto $arg) {
                return $arg->search === null
                    && $arg->perPage === 15
                    && $arg->sortBy === 'id'
                    && $arg->sortDirection === 'asc';
            }))
            ->andReturn([]);

        ($this->controller)($this->makeRequest());
    }

    private function makeRequest(array $params = []): OrderListRequest
    {
        return OrderListRequest::createFrom(
            Request::create('/api/orders', 'GET', $params)
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
