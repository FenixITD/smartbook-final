<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Review;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewResponseDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\Review\StorePublicReviewService;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class StorePublicReviewServiceTest extends TestCase
{
    private ReviewRepositoryInterface&MockInterface $repository;
    private StorePublicReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->service = new StorePublicReviewService($this->repository);
    }

    public function test_creates_review_when_no_existing(): void
    {
        $dto = new ReviewDto(userId: 1, bookId: 10, rating: 5, comment: 'Great!');
        $expected = $this->makeReviewResponse();

        $this->repository->expects('findByUserAndBook')->with(1, 10)->andReturn(null);
        $this->repository->expects('create')->with($dto)->andReturn($expected);

        $result = $this->service->execute($dto);

        $this->assertSame(5.0, $result->rating);
    }

    public function test_throws_when_review_already_exists(): void
    {
        $dto = new ReviewDto(userId: 1, bookId: 10, rating: 5, comment: 'Great!');

        $this->repository->expects('findByUserAndBook')->with(1, 10)->andReturn($this->makeReviewResponse());

        $this->expectException(ValidationException::class);
        $this->service->execute($dto);
    }

    public function test_throws_on_duplicate_key_exception(): void
    {
        $dto = new ReviewDto(userId: 1, bookId: 10, rating: 5, comment: 'Great!');

        $this->repository->expects('findByUserAndBook')->with(1, 10)->andReturn(null);

        $pdoException = new \PDOException('Duplicate entry', 1062);
        $pdoException->errorInfo = ['HY000', 1062, 'Duplicate entry'];
        $exception = new QueryException('sqlite', 'INSERT INTO ...', [], $pdoException);
        $this->repository->expects('create')->andThrow($exception);

        $this->expectException(ValidationException::class);
        $this->service->execute($dto);
    }

    private function makeReviewResponse(): ReviewResponseDto
    {
        return new ReviewResponseDto(
            id: 1,
            userId: 1,
            bookId: 10,
            rating: 5,
            comment: 'Great!',
            userName: 'Test User',
            createdAt: now()->toIso8601String(),
            updatedAt: now()->toIso8601String(),
        );
    }
}
