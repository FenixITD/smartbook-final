<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Order;

use App\Dto\Order\OrderFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\Order\GetWebListOrderService;
use App\Services\Order\SearchOrderService;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetWebListOrderServiceTest extends TestCase
{
    private SearchOrderService $searchService;
    private OrderRepositoryInterface $repository;
    private GetWebListOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = Mockery::mock(SearchOrderService::class);
        $this->repository = Mockery::mock(OrderRepositoryInterface::class);
        $this->service = new GetWebListOrderService($this->searchService, $this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_paginated_from_repository_when_search_is_empty(): void
    {
        $filters = new OrderFiltersDto(search: null, perPage: 15);
        $expected = PaginatedResponseDto::empty(15);

        $this->repository->shouldReceive('getWebList')
            ->once()
            ->with($filters)
            ->andReturn($expected);

        $result = $this->service->get($filters);

        $this->assertSame($expected, $result);
    }

    public function test_returns_empty_paginated_response_when_search_returns_no_ids(): void
    {
        $filters = new OrderFiltersDto(search: 'User', perPage: 15);

        $this->searchService->shouldReceive('search')
            ->once()
            ->with($filters)
            ->andReturn([]);

        $result = $this->service->get($filters);

        $this->assertEquals([], $result->items);
    }

    public function test_returns_paginated_response_from_repository_when_ids_found(): void
    {
        $filters = new OrderFiltersDto(search: 'User', perPage: 15);
        $ids = [5];
        $expected = PaginatedResponseDto::empty(15);

        $this->searchService->shouldReceive('search')
            ->once()
            ->with($filters)
            ->andReturn($ids);

        $this->repository->shouldReceive('getWebListByIds')
            ->once()
            ->with($ids, $filters)
            ->andReturn($expected);

        $result = $this->service->get($filters);

        $this->assertSame($expected, $result);
    }
}
