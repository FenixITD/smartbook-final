<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Review;

use App\Dto\PaginatedResponseDto;
use App\Dto\Review\ReviewFiltersDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\Review\GetWebListReviewService;
use App\Services\Review\SearchReviewService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class GetWebListReviewServiceTest extends TestCase
{
    private SearchReviewService&MockInterface $searchService;
    private ReviewRepositoryInterface&MockInterface $repository;
    private GetWebListReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = Mockery::mock(SearchReviewService::class);
        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->service = new GetWebListReviewService($this->searchService, $this->repository);
    }

    public function test_get_returns_empty_paginated_when_no_ids_found(): void
    {
        $filters = new ReviewFiltersDto();
        $this->searchService->expects('search')->with($filters)->andReturn([]);

        $result = $this->service->get($filters);

        $this->assertSame([], $result->items);
        $this->assertSame(0, $result->total);
    }

    public function test_get_returns_repo_results_when_ids_found(): void
    {
        $filters = new ReviewFiltersDto();
        $paginated = new PaginatedResponseDto([], 0, 15, 1, 1);
        $this->searchService->expects('search')->with($filters)->andReturn([1, 2]);
        $this->repository->expects('getWebListByIds')->with([1, 2], $filters)->andReturn($paginated);

        $result = $this->service->get($filters);

        $this->assertSame($paginated, $result);
    }
}
