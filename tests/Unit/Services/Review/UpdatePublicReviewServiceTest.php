<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Review;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewResponseDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\Review\UpdatePublicReviewService;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class UpdatePublicReviewServiceTest extends TestCase
{
    private ReviewRepositoryInterface&MockInterface $repository;
    private UpdatePublicReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->service = new UpdatePublicReviewService($this->repository);
    }

    public function test_updates_review_when_owned_by_user(): void
    {
        $review = $this->makeReviewResponse(userId: 1);
        $dto = new ReviewDto(userId: 1, bookId: 5, rating: 2, comment: 'Updated');

        $this->repository->expects('getById')->with(10)->andReturn($review);
        $this->repository->expects('update')->with(10, Mockery::on(fn ($d) => $d->rating == 2 && $d->comment === 'Updated'))->once();

        $this->service->execute(10, $dto);

        $this->assertTrue(true);
    }

    public function test_throws_403_when_review_not_found(): void
    {
        $dto = new ReviewDto(userId: 1, bookId: 5, rating: 4, comment: 'Good');

        $this->repository->expects('getById')->with(999)->andReturn(null);

        try {
            $this->service->execute(999, $dto);
            $this->fail('HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_throws_403_when_user_does_not_own_review(): void
    {
        $review = $this->makeReviewResponse(userId: 2);
        $dto = new ReviewDto(userId: 1, bookId: 5, rating: 4, comment: 'Good');

        $this->repository->expects('getById')->with(10)->andReturn($review);

        try {
            $this->service->execute(10, $dto);
            $this->fail('HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    private function makeReviewResponse(int $userId): ReviewResponseDto
    {
        return new ReviewResponseDto(
            id: 10,
            userId: $userId,
            bookId: 5,
            rating: 4,
            comment: 'Good book',
            userName: 'Test User',
            createdAt: now()->toIso8601String(),
            updatedAt: now()->toIso8601String(),
        );
    }
}
