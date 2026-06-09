<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Book;

use App\Dto\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Book\SearchBookByQueryService;
use App\Services\Book\SearchSuggestBookService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchSuggestBookServiceTest extends TestCase
{
    private BookRepositoryInterface&MockInterface $repository;
    private SearchBookByQueryService&MockInterface $searchService;
    private SearchSuggestBookService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(BookRepositoryInterface::class);
        $this->searchService = Mockery::mock(SearchBookByQueryService::class);
        $this->service = new SearchSuggestBookService($this->repository, $this->searchService);
    }

    public function test_returns_empty_array_when_no_ids_found(): void
    {
        $this->searchService->expects('search')->with('query', 5)->andReturn([]);

        $result = $this->service->execute('query');

        $this->assertSame([], $result);
    }

    public function test_returns_mapped_results_when_ids_found(): void
    {
        $book = new BookResponseDto(1, 'Title', 'slug', 1, 'Author Name', 'd', 1.0, 1, null, null, null, null, 'a', '', '');
        $paginated = new PaginatedResponseDto([$book], 1, 5, 1, 1);
        $this->searchService->expects('search')->with('query', 5)->andReturn([1]);
        $this->repository->expects('getOrderedByIds')->with([1], 5)->andReturn($paginated);

        $result = $this->service->execute('query');

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('Title', $result[0]['title']);
        $this->assertSame('Author Name', $result[0]['author']);
        $this->assertArrayHasKey('url', $result[0]);
    }
}
