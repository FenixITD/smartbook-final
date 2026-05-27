<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\CartItems;

use App\Dto\CartItem\CartItemFiltersDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Http\Controllers\Api\CartItems\GetListCartItemController;
use App\Http\Requests\CartItem\CartItemListRequest;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListCartItemControllerTest extends TestCase
{
    private MockInterface&CartItemRepositoryInterface $repository;
    private GetListCartItemController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->app->instance(CartItemRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetListCartItemController::class);
    }

    public function test_returns_200_with_cart_items_list(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->andReturn([
                $this->makeResponseDto(1, 1, 3, 2),
                $this->makeResponseDto(2, 2, 5, 1),
            ]);

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_all_cart_items(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->andReturn([
                $this->makeResponseDto(1, 1, 3, 2),
                $this->makeResponseDto(2, 2, 5, 1),
                $this->makeResponseDto(3, 3, 7, 4),
            ]);

        $response = ($this->controller)($this->makeRequest());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertCount(3, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame(1, $data[0]['userId']);
        $this->assertSame(3, $data[0]['bookId']);
        $this->assertSame(2, $data[0]['quantity']);
        $this->assertSame(2, $data[1]['id']);
        $this->assertSame(3, $data[2]['id']);
    }

    public function test_returns_empty_data_array_when_no_cart_items(): void
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
            ->with(Mockery::on(function (CartItemFiltersDto $arg) {
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
            ->with(Mockery::on(function (CartItemFiltersDto $arg) {
                return $arg->search === null
                    && $arg->perPage === 15
                    && $arg->sortBy === 'id'
                    && $arg->sortDirection === 'asc';
            }))
            ->andReturn([]);

        ($this->controller)($this->makeRequest());
    }

    private function makeRequest(array $params = []): CartItemListRequest
    {
        return CartItemListRequest::createFrom(
            Request::create('/api/cartItems', 'GET', $params)
        );
    }

    private function makeResponseDto(int $id, int $userId, int $bookId, int $quantity): CartItemResponseDto
    {
        return new CartItemResponseDto(
            id: $id,
            userId: $userId,
            bookId: $bookId,
            quantity: $quantity,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
