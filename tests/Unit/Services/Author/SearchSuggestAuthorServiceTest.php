<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Author;

use App\Dto\Author\AuthorResponseDto;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Services\Author\SearchAuthorByQueryService;
use App\Services\Author\SearchSuggestAuthorService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchSuggestAuthorServiceTest extends TestCase
{
    private AuthorRepositoryInterface&MockInterface $repository;
    private SearchAuthorByQueryService&MockInterface $searchService;
    private SearchSuggestAuthorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->searchService = Mockery::mock(SearchAuthorByQueryService::class);
        $this->service = new SearchSuggestAuthorService($this->repository, $this->searchService);
    }

    public function test_returns_empty_array_when_search_returns_no_ids(): void
    {
        $this->searchService
            ->expects('search')
            ->with('unknown', 5)
            ->andReturn([]);

        $this->repository->expects('suggest')->never();

        $result = $this->service->execute('unknown');

        $this->assertSame([], $result);
    }

    public function test_returns_mapped_authors_when_ids_found(): void
    {
        $this->searchService
            ->expects('search')
            ->andReturn([1, 2]);

        $authors = [
            new AuthorResponseDto(id: 1, name: 'Leo Tolstoy', createdAt: '2024-01-01 00:00:00', updatedAt: '2024-01-01 00:00:00'),
            new AuthorResponseDto(id: 2, name: 'Anton Chekhov', createdAt: '2024-01-01 00:00:00', updatedAt: '2024-01-01 00:00:00'),
        ];

        $this->repository
            ->expects('suggest')
            ->with('Tol')
            ->andReturn($authors);

        $result = $this->service->execute('Tol');

        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('Leo Tolstoy', $result[0]['name']);
        $this->assertArrayHasKey('url', $result[0]);
    }

    public function test_result_is_re_indexed(): void
    {
        $this->searchService
            ->expects('search')
            ->andReturn([5]);

        $authors = [
            3 => new AuthorResponseDto(id: 5, name: 'Gogol', createdAt: '2024-01-01 00:00:00', updatedAt: '2024-01-01 00:00:00'),
        ];

        $this->repository
            ->expects('suggest')
            ->andReturn($authors);

        $result = $this->service->execute('Go');

        $this->assertArrayHasKey(0, $result);
        $this->assertArrayNotHasKey(3, $result);
    }

    public function test_does_not_call_repository_when_ids_are_empty(): void
    {
        $this->searchService
            ->expects('search')
            ->andReturn([]);

        $this->repository->expects('suggest')->never();

        $this->service->execute('query');
    }

    public function test_passes_query_to_search_service_with_limit_5(): void
    {
        $this->searchService
            ->expects('search')
            ->with('Dostoevsky', 5)
            ->andReturn([]);

        $this->service->execute('Dostoevsky');
    }

    public function test_passes_query_to_repository_suggest(): void
    {
        $this->searchService
            ->expects('search')
            ->andReturn([1]);

        $this->repository
            ->expects('suggest')
            ->with('Bulgakov')
            ->andReturn([
                new AuthorResponseDto(id: 1, name: 'Bulgakov', createdAt: '2024-01-01 00:00:00', updatedAt: '2024-01-01 00:00:00'),
            ]);

        $this->service->execute('Bulgakov');
    }

    public function test_each_result_contains_id_name_and_url_keys(): void
    {
        $this->searchService
            ->expects('search')
            ->andReturn([1]);

        $this->repository
            ->expects('suggest')
            ->andReturn([
                new AuthorResponseDto(id: 1, name: 'Author', createdAt: '2024-01-01 00:00:00', updatedAt: '2024-01-01 00:00:00'),
            ]);

        $result = $this->service->execute('Author');

        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('url', $result[0]);
    }
}
