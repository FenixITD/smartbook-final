<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Author;

use App\Dto\Author\AuthorFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Services\Author\FetchWebListAuthorService;
use App\Services\Author\SearchAuthorService;
use Mockery;
use PHPUnit\Framework\TestCase;

class FetchWebListAuthorServiceTest extends TestCase
{
    private SearchAuthorService $searchService;
    private AuthorRepositoryInterface $repository;
    private FetchWebListAuthorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = Mockery::mock(SearchAuthorService::class);
        $this->repository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->service = new FetchWebListAuthorService($this->searchService, $this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_paginated_from_repository_when_search_is_empty(): void
    {
        $filters = new AuthorFiltersDto(search: null, perPage: 15);
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
        $filters = new AuthorFiltersDto(search: 'John', perPage: 15);

        $this->searchService->shouldReceive('search')
            ->once()
            ->with($filters)
            ->andReturn([]);

        $result = $this->service->get($filters);

        $this->assertEquals([], $result->items);
        $this->assertEquals(0, $result->total);
        $this->assertEquals(15, $result->perPage);
    }

    public function test_returns_paginated_response_from_repository_when_ids_found(): void
    {
        $filters = new AuthorFiltersDto(search: 'John', perPage: 15);
        $ids = [1, 2, 3];
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
