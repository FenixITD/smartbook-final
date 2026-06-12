<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Genre;

use App\Dto\Genre\GenreFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Genre\GetWebListGenreService;
use App\Services\Genre\SearchGenreService;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetWebListGenreServiceTest extends TestCase
{
    private SearchGenreService $searchService;
    private GenreRepositoryInterface $repository;
    private GetWebListGenreService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = Mockery::mock(SearchGenreService::class);
        $this->repository = Mockery::mock(GenreRepositoryInterface::class);
        $this->service = new GetWebListGenreService($this->searchService, $this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_paginated_from_repository_when_search_is_empty(): void
    {
        $filters = new GenreFiltersDto(search: null, perPage: 15);
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
        $filters = new GenreFiltersDto(search: 'Fantasy', perPage: 15);

        $this->searchService->shouldReceive('search')
            ->once()
            ->with($filters)
            ->andReturn([]);

        $result = $this->service->get($filters);

        $this->assertEquals([], $result->items);
    }

    public function test_returns_paginated_response_from_repository_when_ids_found(): void
    {
        $filters = new GenreFiltersDto(search: 'Fantasy', perPage: 15);
        $ids = [1, 2];
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
