<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Reviews;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewResponseDto;
use App\Http\Controllers\Api\Reviews\UpdateReviewController;
use App\Http\Requests\Review\ReviewDataRequest;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\Request;
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

    public function test_returns_200_with_updated_review(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(4, Mockery::type(ReviewDto::class))
            ->andReturn($this->makeResponseDto(id: 4, rating: 3.5, comment: 'Updated comment'));

        $response = ($this->controller)($this->makeRequest(['rating' => 3.5, 'comment' => 'Updated comment']), 4);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_updated_review_data(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->andReturn($this->makeResponseDto(id: 4, rating: 3.5, comment: 'Updated comment'));

        $response = ($this->controller)($this->makeRequest(['rating' => 3.5, 'comment' => 'Updated comment']), 4);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(4, $data['id']);
        $this->assertSame(3.5, $data['rating']);
        $this->assertSame('Updated comment', $data['comment']);
    }

    public function test_passes_correct_id_and_dto_to_repository(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(
                7,
                Mockery::on(function (ReviewDto $arg) {
                    return $arg->userId === 2
                        && $arg->bookId === 5
                        && $arg->rating === 2.0
                        && $arg->comment === 'Disappointing';
                }),
            )
            ->andReturn($this->makeResponseDto(id: 7));

        ($this->controller)($this->makeRequest([
            'userId' => 2,
            'bookId' => 5,
            'rating' => 2.0,
            'comment' => 'Disappointing',
        ]), 7);
    }

    private function makeRequest(array $data = []): ReviewDataRequest
    {
        $defaults = [
            'userId' => 1,
            'bookId' => 1,
            'rating' => 5.0,
            'comment' => 'Excellent',
        ];

        return ReviewDataRequest::createFrom(
            Request::create('/api/reviews/1', 'PUT', array_merge($defaults, $data))
        );
    }

    private function makeResponseDto(
        int $id = 1,
        float $rating = 5.0,
        string $comment = 'Excellent',
    ): ReviewResponseDto {
        return new ReviewResponseDto(
            id: $id,
            userId: 1,
            bookId: 1,
            userName: 'John Doe',
            rating: $rating,
            comment: $comment,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
