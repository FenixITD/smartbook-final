<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Order;

use App\Dto\Order\OrderResponseDto;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\Order\SearchOrderByQueryService;
use App\Services\Order\SearchSuggestOrderService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchSuggestOrderServiceTest extends TestCase
{
    private OrderRepositoryInterface&MockInterface $repository;
    private SearchOrderByQueryService&MockInterface $searchService;
    private SearchSuggestOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(OrderRepositoryInterface::class);
        $this->searchService = Mockery::mock(SearchOrderByQueryService::class);
        $this->service = new SearchSuggestOrderService($this->repository, $this->searchService);
    }

    public function test_returns_empty_array_when_no_ids_found(): void
    {
        $this->searchService->expects('search')->with('query', 5)->andReturn([]);

        $result = $this->service->execute('query');

        $this->assertSame([], $result);
    }

    public function test_returns_mapped_results_when_ids_found(): void
    {
        $order = new OrderResponseDto(1, 1, 'Name', '10.0', 'status', 'address', 'method', 'date', 'date');
        $this->searchService->expects('search')->with('query', 5)->andReturn([1]);
        $this->repository->expects('getByIds')->with([1])->andReturn([$order]);

        $result = $this->service->execute('query');

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('Name', $result[0]['user_name']);
        $this->assertSame('status', $result[0]['status']);
        $this->assertArrayHasKey('url', $result[0]);
    }
}
