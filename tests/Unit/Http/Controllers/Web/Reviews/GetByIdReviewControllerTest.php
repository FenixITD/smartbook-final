<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Reviews;

use App\Dto\Review\ReviewResponseDto;
use App\Http\Controllers\Web\Reviews\GetByIdReviewController;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetByIdReviewControllerTest extends TestCase
{
    private MockInterface&ReviewRepositoryInterface $repository;
    private GetByIdReviewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->app->instance(ReviewRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetByIdReviewController::class);
    }

    public function test_returns_view_with_review_data(): void
    {
        $dto = $this->makeResponseDto(5);

        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with(5)
            ->andReturn($dto);

        $response = ($this->controller)(5);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('reviews.show', $response->name());
        $this->assertArrayHasKey('review', $response->getData());
        $this->assertSame($dto, $response->getData()['review']);
    }

    private function makeResponseDto(int $id): ReviewResponseDto
    {
        return new ReviewResponseDto(
            id: $id,
            userId: 1,
            bookId: 1,
            userName: 'John Doe',
            rating: 4.5,
            comment: 'Comment',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
