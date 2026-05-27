<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Books;

use App\Dto\Book\BookResponseDto;
use App\Http\Controllers\Web\Books\GetByIdBookController;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetByIdBookControllerTest extends TestCase
{
    private MockInterface&BookRepositoryInterface $repository;
    private GetByIdBookController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(BookRepositoryInterface::class);
        $this->app->instance(BookRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetByIdBookController::class);
    }

    public function test_returns_view(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($this->makeBookResponseDto(id: 1, title: 'Dune'));

        $response = ($this->controller)(1);

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_returns_correct_view_name(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($this->makeBookResponseDto(id: 1, title: 'Dune'));

        $response = ($this->controller)(1);

        $this->assertSame('books.show', $response->name());
    }

    public function test_view_contains_book_key(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($this->makeBookResponseDto(id: 1, title: 'Dune'));

        $response = ($this->controller)(1);

        $this->assertArrayHasKey('book', $response->getData());
    }

    public function test_view_contains_correct_book_data(): void
    {
        $dto = $this->makeBookResponseDto(id: 3, title: '1984');

        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($dto);

        $response = ($this->controller)(3);
        $book = $response->getData()['book'];

        $this->assertSame(3, $book->id);
        $this->assertSame('1984', $book->title);
    }

    public function test_calls_repository_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with(7)
            ->andReturn($this->makeBookResponseDto(id: 7, title: 'Book'));

        ($this->controller)(7);
    }

    private function makeBookResponseDto(int $id, string $title): BookResponseDto
    {
        return new BookResponseDto(
            id: $id,
            title: $title,
            slug: 'test-slug',
            authorId: 1,
            authorName: 'Test Author',
            description: 'Test description',
            price: 19.99,
            stock: 10,
            publishYear: null,
            coverImage: null,
            averageRating: null,
            ratingsCount: null,
            status: 'active',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
