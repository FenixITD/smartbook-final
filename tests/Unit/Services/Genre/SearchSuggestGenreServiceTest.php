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

    public function test_returns_empty_array_when_search_returns_no_ids(): void
    {
        $this->searchService
            ->expects('search')
            ->with('unknown', 5)
            ->andReturn([]);

        $this->repository->expects('getByIds')->never();

        $result = $this->service->execute('unknown');

        $this->assertSame([], $result);
    }

    public function test_returns_mapped_genres_when_ids_found(): void
    {
        $this->searchService
            ->expects('search')
            ->andReturn([1, 2]);

        $genres = [
            new GenreResponseDto(id: 1, name: 'Fantasy', slug: 'fantasy', createdAt: '2024-01-01 00:00:00', updatedAt: '2024-01-01 00:00:00'),
            new GenreResponseDto(id: 2, name: 'Sci-Fi', slug: 'sci-fi', createdAt: '2024-01-01 00:00:00', updatedAt: '2024-01-01 00:00:00'),
        ];

        $this->repository
            ->expects('getByIds')
            ->with([1, 2])
            ->andReturn($genres);

        $result = $this->service->execute('Fan');

        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('Fantasy', $result[0]['name']);
        $this->assertArrayHasKey('url', $result[0]);
    }

    public function test_result_is_re_indexed(): void
    {
        $this->searchService
            ->expects('search')
            ->andReturn([5]);

        $genres = [
            3 => new GenreResponseDto(id: 5, name: 'Horror', slug: 'horror', createdAt: '2024-01-01 00:00:00', updatedAt: '2024-01-01 00:00:00'),
        ];

        $this->repository
            ->expects('getByIds')
            ->with([5])
            ->andReturn($genres);

        $result = $this->service->execute('Hor');

        $this->assertArrayHasKey(0, $result);
        $this->assertArrayNotHasKey(3, $result);
    }

    public function test_does_not_call_repository_when_ids_are_empty(): void
    {
        $this->searchService
            ->expects('search')
            ->andReturn([]);

        $this->repository->expects('getByIds')->never();

        $this->service->execute('query');
    }

    public function test_passes_query_to_search_service_with_limit_5(): void
    {
        $this->searchService
            ->expects('search')
            ->with('Horror', 5)
            ->andReturn([]);

        $this->service->execute('Horror');
    }

    public function test_passes_ids_to_repository_getByIds(): void
    {
        $this->searchService
            ->expects('search')
            ->andReturn([1]);

        $this->repository
            ->expects('getByIds')
            ->with([1])
            ->andReturn([
                new GenreResponseDto(id: 1, name: 'Drama', slug: 'drama', createdAt: '2024-01-01 00:00:00', updatedAt: '2024-01-01 00:00:00'),
            ]);

        $this->service->execute('Drama');
    }

    public function test_each_result_contains_id_name_and_url_keys(): void
    {
        $this->searchService
            ->expects('search')
            ->andReturn([1]);

        $this->repository
            ->expects('getByIds')
            ->with([1])
            ->andReturn([
                new GenreResponseDto(id: 1, name: 'Genre', slug: 'genre', createdAt: '2024-01-01 00:00:00', updatedAt: '2024-01-01 00:00:00'),
            ]);

        $result = $this->service->execute('Genre');

        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('url', $result[0]);
    }
}
