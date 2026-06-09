<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Order;

use App\Dto\Order\OrderFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\Order\GetWebListOrderService;
use App\Services\Order\SearchOrderService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class GetWebListOrderServiceTest extends TestCase
{
    private SearchOrderService&MockInterface $searchService;
    private OrderRepositoryInterface&MockInterface $repository;
    private GetWebListOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = Mockery::mock(SearchOrderService::class);
        $this->repository = Mockery::mock(OrderRepositoryInterface::class);
        $this->service = new GetWebListOrderService($this->searchService, $this->repository);
    }

    public function test_returns_empty_paginated_when_no_ids_found(): void
    {
        $filters = new OrderFiltersDto();
        $this->searchService->expects('search')->with($filters)->andReturn([]);

        $result = $this->service->get($filters);

        $this->assertSame([], $result->items);
        $this->assertSame(0, $result->total);
    }

    public function test_returns_repo_results_when_ids_found(): void
    {
        $filters = new OrderFiltersDto();
        $paginated = new PaginatedResponseDto([], 0, 15, 1, 1);
        $this->searchService->expects('search')->with($filters)->andReturn([1, 2]);
        $this->repository->expects('getWebListByIds')->with([1, 2], $filters)->andReturn($paginated);

        $result = $this->service->get($filters);

        $this->assertSame($paginated, $result);
    }
}
