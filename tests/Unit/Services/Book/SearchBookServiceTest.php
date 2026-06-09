<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Book;

use App\Dto\Book\BookFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Book\SearchBookByQueryService;
use App\Services\Book\SearchBookService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchBookServiceTest extends TestCase
{
    private BookRepositoryInterface&MockInterface $repository;
    private SearchBookByQueryService&MockInterface $searchService;
    private SearchBookService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(BookRepositoryInterface::class);
        $this->searchService = Mockery::mock(SearchBookByQueryService::class);
        $this->service = new SearchBookService($this->repository, $this->searchService);
    }

    public function test_fetch_list_returns_repo_results_when_search_is_null(): void
    {
        $filters = new BookFiltersDto();
        $this->repository->expects('getList')->with($filters)->andReturn([]);

        $result = $this->service->fetchList($filters);

        $this->assertSame([], $result);
    }

    public function test_fetch_list_returns_empty_when_search_yields_no_ids(): void
    {
        $filters = new BookFiltersDto(search: 'query');
        $this->searchService->expects('search')->with('query')->andReturn([]);

        $result = $this->service->fetchList($filters);

        $this->assertSame([], $result);
    }

    public function test_fetch_list_returns_repo_results_when_ids_found(): void
    {
        $filters = new BookFiltersDto(search: 'query');
        $this->searchService->expects('search')->with('query')->andReturn([1, 2]);
        $this->repository->expects('getListByIds')->with([1, 2], $filters)->andReturn([]);

        $result = $this->service->fetchList($filters);

        $this->assertSame([], $result);
    }

    public function test_fetch_web_list_returns_repo_results_when_search_is_null(): void
    {
        $filters = new BookFiltersDto();
        $paginated = new PaginatedResponseDto([], 0, 15, 1, 1);
        $this->repository->expects('getWebList')->with($filters)->andReturn($paginated);

        $result = $this->service->fetchWebList($filters);

        $this->assertSame($paginated, $result);
    }

    public function test_fetch_web_list_returns_empty_paginated_when_search_yields_no_ids(): void
    {
        $filters = new BookFiltersDto(search: 'query');
        $this->searchService->expects('search')->with('query')->andReturn([]);

        $result = $this->service->fetchWebList($filters);

        $this->assertSame([], $result->items);
        $this->assertSame(0, $result->total);
    }

    public function test_fetch_web_list_returns_repo_results_when_ids_found(): void
    {
        $filters = new BookFiltersDto(search: 'query');
        $paginated = new PaginatedResponseDto([], 0, 15, 1, 1);
        $this->searchService->expects('search')->with('query')->andReturn([1, 2]);
        $this->repository->expects('getWebListByIds')->with([1, 2], $filters)->andReturn($paginated);

        $result = $this->service->fetchWebList($filters);

        $this->assertSame($paginated, $result);
    }
}
