<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Review;

use App\Dto\Review\ReviewResponseDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\Review\SearchReviewByQueryService;
use App\Services\Review\SearchSuggestReviewService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchSuggestReviewServiceTest extends TestCase
{
    private ReviewRepositoryInterface&MockInterface $repository;
    private SearchReviewByQueryService&MockInterface $searchService;
    private SearchSuggestReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->searchService = Mockery::mock(SearchReviewByQueryService::class);
        $this->service = new SearchSuggestReviewService($this->repository, $this->searchService);
    }

    public function test_execute_returns_empty_array_when_no_ids_found(): void
    {
        $this->searchService->expects('search')->with('query', 5)->andReturn([]);

        $result = $this->service->execute('query');

        $this->assertSame([], $result);
    }

    public function test_execute_returns_mapped_results_when_ids_found(): void
    {
        $review = new ReviewResponseDto(1, 2, 3, 'Name', 5.0, 'Short comment', 'date', 'date');
        $this->searchService->expects('search')->with('query', 5)->andReturn([1]);
        $this->repository->expects('getByIds')->with([1])->andReturn([$review]);

        $result = $this->service->execute('query');

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('Name', $result[0]['user_name']);
        $this->assertSame('Short comment...', $result[0]['content']);
        $this->assertArrayHasKey('url', $result[0]);
    }
}
