<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Reviews;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewResponseDto;
use App\Http\Controllers\Web\Reviews\CreateReviewController;
use App\Http\Requests\Review\ReviewDataRequest;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateReviewControllerTest extends TestCase
{
    private MockInterface&ReviewRepositoryInterface $repository;
    private CreateReviewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->app->instance(ReviewRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(CreateReviewController::class);
    }

    public function test_create_returns_view(): void
    {
        $response = $this->controller->create();

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('reviews.create', $response->name());
    }

    public function test_store_calls_repository_create_and_redirects(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (ReviewDto $dto) {
                return $dto->userId === 1
                    && $dto->bookId === 2
                    && $dto->rating === 5.0
                    && $dto->comment === 'Great book';
            }))
            ->andReturn($this->makeResponseDto(1));

        $response = $this->controller->store($this->makeRequest([
            'userId' => 1,
            'bookId' => 2,
            'rating' => 5.0,
            'comment' => 'Great book',
        ]));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('reviews.index'), $response->getTargetUrl());
    }

    private function makeRequest(array $data): ReviewDataRequest
    {
        return ReviewDataRequest::createFrom(
            Request::create('/reviews', 'POST', $data)
        );
    }

    private function makeResponseDto(int $id): ReviewResponseDto
    {
        return new ReviewResponseDto(
            id: $id,
            userId: 1,
            bookId: 2,
            userName: 'John Doe',
            rating: 5.0,
            comment: 'Great book',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
