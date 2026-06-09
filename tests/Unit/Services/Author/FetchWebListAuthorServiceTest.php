<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Author;

use App\Dto\Author\AuthorFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Services\Author\FetchWebListAuthorService;
use App\Services\Author\SearchAuthorService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class FetchWebListAuthorServiceTest extends TestCase
{
    private SearchAuthorService&MockInterface $searchService;
    private AuthorRepositoryInterface&MockInterface $repository;
    private FetchWebListAuthorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchService = Mockery::mock(SearchAuthorService::class);
        $this->repository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->service = new FetchWebListAuthorService($this->searchService, $this->repository);
    }

    public function test_returns_empty_paginated_response_when_search_returns_no_ids(): void
    {
        $filters = new AuthorFiltersDto(perPage: 10);

        $this->searchService
            ->expects('search')
            ->with($filters)
            ->andReturn([]);

        $this->repository->expects('getWebListByIds')->never();

        $result = $this->service->get($filters);

        $this->assertInstanceOf(PaginatedResponseDto::class, $result);
        $this->assertSame([], $result->items);
        $this->assertSame(0, $result->total);
        $this->assertSame(10, $result->perPage);
    }

    public function test_returns_paginated_response_from_repository_when_ids_found(): void
    {
        $filters = new AuthorFiltersDto(perPage: 15);
        $ids = [1, 2, 3];

        $expected = new PaginatedResponseDto(
            items: [],
            total: 3,
            perPage: 15,
            currentPage: 1,
            lastPage: 1,
        );

        $this->searchService
            ->expects('search')
            ->with($filters)
            ->andReturn($ids);

        $this->repository
            ->expects('getWebListByIds')
            ->with($ids, $filters)
            ->andReturn($expected);

        $result = $this->service->get($filters);

        $this->assertSame($expected, $result);
    }

    public function test_does_not_call_repository_when_search_returns_empty_array(): void
    {
        $filters = new AuthorFiltersDto();

        $this->searchService
            ->expects('search')
            ->andReturn([]);

        $this->repository->expects('getWebListByIds')->never();

        $this->service->get($filters);
    }

    public function test_passes_filters_to_search_service(): void
    {
        $filters = new AuthorFiltersDto(search: 'Bulgakov', perPage: 5);

        $this->searchService
            ->expects('search')
            ->with($filters)
            ->andReturn([]);

        $this->service->get($filters);
    }

    public function test_passes_ids_and_filters_to_repository(): void
    {
        $filters = new AuthorFiltersDto(search: 'Gogol');
        $ids = [10, 20];

        $paginated = new PaginatedResponseDto(
            items: [],
            total: 2,
            perPage: 15,
            currentPage: 1,
            lastPage: 1,
        );

        $this->searchService
            ->expects('search')
            ->andReturn($ids);

        $this->repository
            ->expects('getWebListByIds')
            ->with($ids, $filters)
            ->andReturn($paginated);

        $this->service->get($filters);
    }

    public function test_empty_response_uses_per_page_from_filters(): void
    {
        $filters = new AuthorFiltersDto(perPage: 25);

        $this->searchService
            ->expects('search')
            ->andReturn([]);

        $result = $this->service->get($filters);

        $this->assertSame(25, $result->perPage);
    }
}
