<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Reviews;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewResponseDto;
use App\Http\Controllers\Web\Reviews\StorePublicReviewController;
use App\Http\Requests\Review\StorePublicReviewRequest;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class StorePublicReviewControllerTest extends TestCase
{
    private MockInterface&ReviewRepositoryInterface $repository;
    private StorePublicReviewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->app->instance(ReviewRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(StorePublicReviewController::class);

        $this->app->make('session')->start();
    }

    public function test_store_public_review_calls_repository_and_redirects_back(): void
    {
        Auth::shouldReceive('id')->once()->andReturn(7);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (ReviewDto $dto) {
                return $dto->userId === 7
                    && $dto->bookId === 15
                    && $dto->rating === 5.0
                    && $dto->comment === 'Amazing book!';
            }))
            ->andReturn($this->makeResponseDto(1));

        $response = ($this->controller)($this->makeRequest([
            'book_id' => 15,
            'rating' => 5,
            'comment' => 'Amazing book!',
        ]));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));
    }

    private function makeRequest(array $data): StorePublicReviewRequest
    {
        $request = StorePublicReviewRequest::createFrom(
            Request::create('/catalog/reviews', 'POST', $data)
        );

        $session = $this->app->make('session.store');
        $request->setLaravelSession($session);

        return $request;
    }

    private function makeResponseDto(int $id): ReviewResponseDto
    {
        return new ReviewResponseDto(
            id: $id,
            userId: 7,
            bookId: 15,
            userName: 'John Doe',
            rating: 5.0,
            comment: 'Amazing book!',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
