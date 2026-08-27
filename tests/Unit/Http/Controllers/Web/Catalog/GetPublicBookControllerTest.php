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

    private function makeBookDto(int $id = 1, string $status = 'active'): BookResponseDto
    {
        return new BookResponseDto(
            id: $id,
            title: 'Test Book',
            slug: 'test-book',
            authorId: 1,
            authorName: 'Author',
            description: 'Description',
            price: '10.00',
            stock: 5,
            publishYear: 2024,
            coverImage: null,
            averageRating: null,
            ratingsCount: null,
            status: $status,
            createdAt: now()->toDateTimeString(),
            updatedAt: now()->toDateTimeString(),
        );
    }

    public function test_returns_view(): void
    {
        $slug = 'test-book-slug';
        $bookDto = $this->makeBookDto();

        $this->bookRepository
            ->shouldReceive('findBySlugWithRelations')
            ->once()
            ->with($slug)
            ->andReturn($bookDto);

        $this->reviewRepository
            ->shouldReceive('getByBookId')
            ->once()
            ->with($bookDto->id)
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)($slug);

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_returns_correct_view_name(): void
    {
        $slug = 'test-book-slug';
        $bookDto = $this->makeBookDto();

        $this->bookRepository
            ->shouldReceive('findBySlugWithRelations')
            ->andReturn($bookDto);

        $this->reviewRepository
            ->shouldReceive('getByBookId')
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)($slug);

        $this->assertSame('catalog.show', $response->name());
    }

    public function test_calls_repositories_with_correct_slug_and_id(): void
    {
        $slug = 'awesome-book';
        $bookId = 42;
        $bookDto = $this->makeBookDto(id: $bookId);

        $this->bookRepository
            ->shouldReceive('findBySlugWithRelations')
            ->once()
            ->with($slug)
            ->andReturn($bookDto);

        $this->reviewRepository
            ->shouldReceive('getByBookId')
            ->once()
            ->with($bookId)
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($slug);
    }

    public function test_passes_book_and_reviews_data_to_view(): void
    {
        $slug = 'another-book';
        $bookDto = $this->makeBookDto(id: 5);
        $reviewsDto = Mockery::mock(PaginatedResponseDto::class);

        $this->bookRepository
            ->shouldReceive('findBySlugWithRelations')
            ->andReturn($bookDto);

        $this->reviewRepository
            ->shouldReceive('getByBookId')
            ->andReturn($reviewsDto);

        $response = ($this->controller)($slug);
        $data = $response->getData();

        $this->assertArrayHasKey('book', $data);
        $this->assertArrayHasKey('reviews', $data);

        $this->assertSame($bookDto, $data['book']);
        $this->assertSame($reviewsDto, $data['reviews']);
    }
}
