<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Reviews;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewResponseDto;
use App\Http\Controllers\Api\Reviews\CreateReviewController;
use App\Http\Requests\Review\ReviewDataRequest;
use App\Services\Review\StorePublicReviewService;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateReviewControllerTest extends TestCase
{
    private MockInterface&StorePublicReviewService $service;
    private CreateReviewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(StorePublicReviewService::class);
        $this->app->instance(StorePublicReviewService::class, $this->service);
        $this->controller = $this->app->make(CreateReviewController::class);
    }

    public function test_returns_201_with_created_review(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->andReturn($this->makeResponseDto(id: 1));

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_response_contains_created_review_data(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->andReturn($this->makeResponseDto(id: 5, userId: 2, bookId: 3, rating: 4.5, comment: 'Great book'));

        $response = ($this->controller)($this->makeRequest([
            'userId' => 2,
            'bookId' => 3,
            'rating' => 4.5,
            'comment' => 'Great book',
        ]));
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(5, $data['id']);
        $this->assertSame(2, $data['userId']);
        $this->assertSame(3, $data['bookId']);
        $this->assertSame(4.5, $data['rating']);
        $this->assertSame('Great book', $data['comment']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_passes_dto_from_request_to_service(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(function (ReviewDto $arg) {
                return $arg->userId === 7
                    && $arg->bookId === 10
                    && $arg->rating === 3.0
                    && $arg->comment === 'Average read';
            }))
            ->andReturn($this->makeResponseDto(id: 1));

        ($this->controller)($this->makeRequest([
            'userId' => 7,
            'bookId' => 10,
            'rating' => 3.0,
            'comment' => 'Average read',
        ]));
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
            Request::create('/api/reviews', 'POST', array_merge($defaults, $data))
        );
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
