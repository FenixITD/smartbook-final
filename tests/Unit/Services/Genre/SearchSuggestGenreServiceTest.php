<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Genre;

use App\Dto\Genre\GenreResponseDto;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Genre\SearchGenreByQueryService;
use App\Services\Genre\SearchSuggestGenreService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchSuggestGenreServiceTest extends TestCase
{
    private GenreRepositoryInterface&MockInterface $repository;
    private SearchGenreByQueryService&MockInterface $searchService;
    private SearchSuggestGenreService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(GenreRepositoryInterface::class);
        $this->searchService = Mockery::mock(SearchGenreByQueryService::class);
        $this->service = new SearchSuggestGenreService($this->repository, $this->searchService);
    }

    public function test_returns_empty_array_when_no_ids_found(): void
    {
        $this->searchService->expects('search')->with('query', 5)->andReturn([]);

        $result = $this->service->execute('query');

        $this->assertSame([], $result);
    }

    public function test_returns_mapped_results_when_ids_found(): void
    {
        $genre = new GenreResponseDto(1, 'Name', 'slug', 'date', 'date', 0);
        $this->searchService->expects('search')->with('query', 5)->andReturn([1]);
        $this->repository->expects('suggest')->with('query')->andReturn([$genre]);

        $result = $this->service->execute('query');

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('Name', $result[0]['name']);
        $this->assertArrayHasKey('url', $result[0]);
    }
}
