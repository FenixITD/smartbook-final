<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Reviews;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewResponseDto;
use App\Http\Controllers\Web\Reviews\UpdateReviewController;
use App\Http\Requests\Review\ReviewDataRequest;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateReviewControllerTest extends TestCase
{
    private MockInterface&ReviewRepositoryInterface $repository;
    private UpdateReviewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->app->instance(ReviewRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(UpdateReviewController::class);
    }

    public function test_edit_returns_view_with_review_data(): void
    {
        $dto = $this->makeResponseDto(3);

        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with(3)
            ->andReturn($dto);

        $response = $this->controller->edit(3);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('reviews.edit', $response->name());
        $this->assertArrayHasKey('review', $response->getData());
        $this->assertSame($dto, $response->getData()['review']);
    }

    public function test_update_calls_repository_update_and_redirects(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(
                4,
                Mockery::on(function (ReviewDto $dto) {
                    return $dto->userId === 1
                        && $dto->bookId === 2
                        && $dto->rating === 4.0
                        && $dto->comment === 'Updated comment';
                })
            )
            ->andReturn($this->makeResponseDto(4));

        $response = $this->controller->update($this->makeRequest([
            'userId' => 1,
            'bookId' => 2,
            'rating' => 4.0,
            'comment' => 'Updated comment',
        ]), 4);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('reviews.index'), $response->getTargetUrl());
    }

    private function makeRequest(array $data): ReviewDataRequest
    {
        return ReviewDataRequest::createFrom(
            Request::create('/reviews/1', 'PUT', $data)
        );
    }

    private function makeResponseDto(int $id): ReviewResponseDto
    {
        return new ReviewResponseDto(
            id: $id,
            userId: 1,
            bookId: 2,
            userName: 'John Doe',
            rating: 4.0,
            comment: 'Updated comment',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
