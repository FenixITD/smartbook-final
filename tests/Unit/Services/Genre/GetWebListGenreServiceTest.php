<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Genre;

use App\Dto\Genre\GenreFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Genre\GetWebListGenreService;
use App\Services\Genre\SearchGenreService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class GetWebListGenreServiceTest extends TestCase
{
    private SearchGenreService&MockInterface $searchService;
    private GenreRepositoryInterface&MockInterface $repository;
    private GetWebListGenreService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = Mockery::mock(SearchGenreService::class);
        $this->repository = Mockery::mock(GenreRepositoryInterface::class);
        $this->service = new GetWebListGenreService($this->searchService, $this->repository);
    }

    public function test_returns_empty_paginated_when_no_ids_found(): void
    {
        $filters = new GenreFiltersDto();
        $this->searchService->expects('search')->with($filters)->andReturn([]);

        $result = $this->service->get($filters);

        $this->assertSame([], $result->items);
        $this->assertSame(0, $result->total);
    }

    public function test_returns_repo_results_when_ids_found(): void
    {
        $filters = new GenreFiltersDto();
        $paginated = new PaginatedResponseDto([], 0, 15, 1, 1);
        $this->searchService->expects('search')->with($filters)->andReturn([1, 2]);
        $this->repository->expects('getWebListByIds')->with([1, 2], $filters)->andReturn($paginated);

        $result = $this->service->get($filters);

        $this->assertSame($paginated, $result);
    }
}
