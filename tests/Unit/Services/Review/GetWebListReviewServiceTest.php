<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Review;

use App\Dto\Review\ReviewFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\Review\GetWebListReviewService;
use App\Services\Review\SearchReviewService;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetWebListReviewServiceTest extends TestCase
{
    private SearchReviewService $searchService;
    private ReviewRepositoryInterface $repository;
    private GetWebListReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = Mockery::mock(SearchReviewService::class);
        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->service = new GetWebListReviewService($this->searchService, $this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_paginated_from_repository_when_search_is_empty(): void
    {
        $filters = new ReviewFiltersDto(search: null, perPage: 15);
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
        $filters = new ReviewFiltersDto(search: 'Great', perPage: 15);

        $this->searchService->shouldReceive('search')
            ->once()
            ->with($filters)
            ->andReturn([]);

        $result = $this->service->get($filters);

        $this->assertEquals([], $result->items);
    }

    public function test_returns_paginated_response_from_repository_when_ids_found(): void
    {
        $filters = new ReviewFiltersDto(search: 'Great', perPage: 15);
        $ids = [10];
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
