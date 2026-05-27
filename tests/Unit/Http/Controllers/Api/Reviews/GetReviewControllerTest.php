<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Reviews;

use App\Dto\Review\ReviewResponseDto;
use App\Http\Controllers\Api\Reviews\GetReviewController;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetReviewControllerTest extends TestCase
{
    private MockInterface&ReviewRepositoryInterface $repository;
    private GetReviewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->app->instance(ReviewRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetReviewController::class);
    }

    public function test_returns_200_with_review(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(3)
            ->andReturn($this->makeResponseDto(id: 3));

        $response = ($this->controller)(3);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_correct_review_data(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->andReturn($this->makeResponseDto(id: 3, userId: 5, bookId: 8, rating: 4.5, comment: 'Very good'));

        $response = ($this->controller)(3);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(3, $data['id']);
        $this->assertSame(5, $data['userId']);
        $this->assertSame(8, $data['bookId']);
        $this->assertSame(4.5, $data['rating']);
        $this->assertSame('Very good', $data['comment']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_calls_repository_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(42)
            ->andReturn($this->makeResponseDto(id: 42));

        ($this->controller)(42);
    }

    private function makeResponseDto(
        int $id = 1,
        int $userId = 1,
        int $bookId = 1,
        float $rating = 5.0,
        string $comment = 'Excellent',
    ): ReviewResponseDto {
        return new ReviewResponseDto(
            id: $id,
            userId: $userId,
            bookId: $bookId,
            userName: 'John Doe',
            rating: $rating,
            comment: $comment,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
