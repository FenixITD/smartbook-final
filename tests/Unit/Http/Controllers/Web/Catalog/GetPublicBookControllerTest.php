<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Catalog;

use App\Dto\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Http\Controllers\Web\Catalog\GetPublicBookController;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetPublicBookControllerTest extends TestCase
{
    private MockInterface&BookRepositoryInterface $bookRepository;
    private MockInterface&ReviewRepositoryInterface $reviewRepository;
    private GetPublicBookController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookRepository = Mockery::mock(BookRepositoryInterface::class);
        $this->reviewRepository = Mockery::mock(ReviewRepositoryInterface::class);

        $this->app->instance(BookRepositoryInterface::class, $this->bookRepository);
        $this->app->instance(ReviewRepositoryInterface::class, $this->reviewRepository);

        $this->controller = $this->app->make(GetPublicBookController::class);
    }

    public function test_returns_view(): void
    {
        $this->bookRepository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->andReturn(Mockery::mock(BookResponseDto::class));

        $this->reviewRepository
            ->shouldReceive('getByBookId')
            ->once()
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)(1);

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_returns_correct_view_name(): void
    {
        $this->bookRepository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn(Mockery::mock(BookResponseDto::class));

        $this->reviewRepository
            ->shouldReceive('getByBookId')
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)(1);

        $this->assertSame('catalog.show', $response->name());
    }

    public function test_calls_repositories_with_correct_book_id(): void
    {
        $bookId = 42;

        $this->bookRepository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with($bookId)
            ->andReturn(Mockery::mock(BookResponseDto::class));

        $this->reviewRepository
            ->shouldReceive('getByBookId')
            ->once()
            ->with($bookId)
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($bookId);
    }

    public function test_passes_book_and_reviews_data_to_view(): void
    {
        $bookDto = Mockery::mock(BookResponseDto::class);
        $reviewsDto = Mockery::mock(PaginatedResponseDto::class);

        $this->bookRepository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($bookDto);

        $this->reviewRepository
            ->shouldReceive('getByBookId')
            ->andReturn($reviewsDto);

        $response = ($this->controller)(5);
        $data = $response->getData();

        $this->assertArrayHasKey('book', $data);
        $this->assertArrayHasKey('reviews', $data);

        $this->assertSame($bookDto, $data['book']);
        $this->assertSame($reviewsDto, $data['reviews']);
    }
}
