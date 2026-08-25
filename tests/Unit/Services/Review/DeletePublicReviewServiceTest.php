<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Review;

use App\Dto\Review\ReviewResponseDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\Review\DeletePublicReviewService;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class DeletePublicReviewServiceTest extends TestCase
{
    private ReviewRepositoryInterface&MockInterface $repository;
    private TransactionManagerInterface&MockInterface $transactionManager;
    private DeletePublicReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->transactionManager = Mockery::mock(TransactionManagerInterface::class);
        $this->service = new DeletePublicReviewService($this->repository, $this->transactionManager);
    }

    public function test_deletes_review_when_owned_by_user(): void
    {
        $review = $this->makeReviewResponse(userId: 1);

        $this->repository->expects('getById')->with(10)->andReturn($review);
        $this->transactionManager->expects('transaction')->andReturnUsing(fn ($fn) => $fn());
        $this->repository->expects('delete')->with(10)->andReturn(true);

        $this->service->execute(10, 1);

        $this->assertTrue(true);
    }

    public function test_throws_403_when_review_not_found(): void
    {
        $this->repository->expects('getById')->with(999)->andReturn(null);

        try {
            $this->service->execute(999, 1);
            $this->fail('HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_throws_403_when_user_does_not_own_review(): void
    {
        $review = $this->makeReviewResponse(userId: 2);

        $this->repository->expects('getById')->with(10)->andReturn($review);

        try {
            $this->service->execute(10, 1);
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
