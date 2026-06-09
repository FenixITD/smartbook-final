<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Book;

use App\Dto\Dashboard\DashboardFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Book\GetDashboardBooksService;
use App\Services\Book\SearchBookForDashboardService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class GetDashboardBooksServiceTest extends TestCase
{
    private SearchBookForDashboardService&MockInterface $searchService;
    private BookRepositoryInterface&MockInterface $repository;
    private GetDashboardBooksService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = Mockery::mock(SearchBookForDashboardService::class);
        $this->repository = Mockery::mock(BookRepositoryInterface::class);
        $this->service = new GetDashboardBooksService($this->searchService, $this->repository);
    }

    public function test_returns_empty_paginated_when_no_ids_found(): void
    {
        $filters = new DashboardFiltersDto();
        $this->searchService->expects('search')->with($filters)->andReturn([]);

        $result = $this->service->get($filters);

        $this->assertSame([], $result->items);
        $this->assertSame(0, $result->total);
    }

    public function test_returns_repo_results_when_ids_found(): void
    {
        $filters = new DashboardFiltersDto();
        $paginated = new PaginatedResponseDto([], 0, 18, 1, 1);
        $this->searchService->expects('search')->with($filters)->andReturn([1, 2]);
        $this->repository->expects('getDashboardListByIds')->with([1, 2], $filters)->andReturn($paginated);

        $result = $this->service->get($filters);

        $this->assertSame($paginated, $result);
    }
}
