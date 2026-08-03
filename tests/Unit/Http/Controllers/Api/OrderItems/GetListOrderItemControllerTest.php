<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\OrderItems;

use App\Dto\OrderItem\OrderItemFiltersDto;
use App\Dto\OrderItem\OrderItemResponseDto;
use App\Http\Controllers\Api\OrderItems\GetListOrderItemController;
use App\Http\Requests\OrderItem\OrderItemListRequest;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListOrderItemControllerTest extends TestCase
{
    private MockInterface&OrderItemRepositoryInterface $repository;
    private GetListOrderItemController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(OrderItemRepositoryInterface::class);
        $this->app->instance(OrderItemRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetListOrderItemController::class);
    }

    public function test_returns_200_with_order_items_list(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->andReturn([
                $this->makeResponseDto(1, 1, 2, 1, '9.99'),
                $this->makeResponseDto(2, 1, 3, 2, '19.99'),
            ]);

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_all_order_items(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->andReturn([
                $this->makeResponseDto(1, 1, 2, 1, '9.99'),
                $this->makeResponseDto(2, 1, 3, 2, '19.99'),
                $this->makeResponseDto(3, 2, 4, 1, '5.00'),
            ]);

        $response = ($this->controller)($this->makeRequest());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertCount(3, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame('9.99', $data[0]['priceAtPurchase']);
        $this->assertSame(2, $data[1]['id']);
        $this->assertSame(3, $data[2]['id']);
    }

    public function test_returns_empty_data_array_when_no_order_items(): void
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
            ->with(Mockery::on(function (OrderItemFiltersDto $arg) {
                return $arg->search === 'test'
                    && $arg->perPage === 10
                    && $arg->sortBy === 'quantity'
                    && $arg->sortDirection === 'desc';
            }))
            ->andReturn([]);

        ($this->controller)($this->makeRequest([
            'search' => 'test',
            'perPage' => 10,
            'sortBy' => 'quantity',
            'sortDirection' => 'desc',
        ]));
    }

    public function test_uses_default_filters_when_no_query_params(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->with(Mockery::on(function (OrderItemFiltersDto $arg) {
                return $arg->search === null
                    && $arg->perPage === 15
                    && $arg->sortBy === 'id'
                    && $arg->sortDirection === 'asc';
            }))
            ->andReturn([]);

        ($this->controller)($this->makeRequest());
    }

    private function makeRequest(array $params = []): OrderItemListRequest
    {
        return OrderItemListRequest::createFrom(
            Request::create('/api/orderItems', 'GET', $params)
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
