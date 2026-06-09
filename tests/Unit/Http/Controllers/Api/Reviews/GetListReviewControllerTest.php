<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Reviews;

use App\Dto\Review\ReviewFiltersDto;
use App\Dto\Review\ReviewResponseDto;
use App\Http\Controllers\Api\Reviews\GetListReviewController;
use App\Http\Requests\Review\ReviewListRequest;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListReviewControllerTest extends TestCase
{
    private MockInterface&ReviewRepositoryInterface $repository;
    private GetListReviewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->app->instance(ReviewRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetListReviewController::class);
    }

    public function test_returns_200_with_reviews_list(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->andReturn([
                $this->makeResponseDto(1),
                $this->makeResponseDto(2),
            ]);

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_all_reviews(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->andReturn([
                $this->makeResponseDto(1),
                $this->makeResponseDto(2),
                $this->makeResponseDto(3),
            ]);

        $response = ($this->controller)($this->makeRequest());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertCount(3, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame(2, $data[1]['id']);
        $this->assertSame(3, $data[2]['id']);
    }

    public function test_returns_empty_data_array_when_no_reviews(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->andReturn([]);

        $response = ($this->controller)($this->makeRequest());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $data);
    }

    public function test_passes_filters_dto_from_request_to_repository(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->with(Mockery::on(function (ReviewFiltersDto $arg) {
                return $arg->search === 'great book'
                    && $arg->perPage === 10
                    && $arg->sortBy === 'rating'
                    && $arg->sortDirection === 'desc';
            }))
            ->andReturn([]);

        ($this->controller)($this->makeRequest([
            'search' => 'great book',
            'perPage' => 10,
            'sortBy' => 'rating',
            'sortDirection' => 'desc',
        ]));
    }

    public function test_uses_default_filters_when_no_query_params(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->with(Mockery::on(function (ReviewFiltersDto $arg) {
                return $arg->search === null
                    && $arg->perPage === 15
                    && $arg->sortBy === 'id'
                    && $arg->sortDirection === 'desc';
            }))
            ->andReturn([]);

        ($this->controller)($this->makeRequest());
    }

    private function makeRequest(array $params = []): ReviewListRequest
    {
        return ReviewListRequest::createFrom(
            Request::create('/api/reviews', 'GET', $params)
        );
    }

    private function makeResponseDto(int $id): ReviewResponseDto
    {
        return new ReviewResponseDto(
            id: $id,
            userId: 1,
            bookId: 1,
            userName: 'John Doe',
            rating: 5.0,
            comment: 'Excellent',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
