<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Books;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookResponseDto;
use App\Http\Controllers\Web\Books\UpdateBookController;
use App\Http\Requests\Book\BookWebDataRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Book\UpdateBookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateBookControllerTest extends TestCase
{
    private MockInterface&BookRepositoryInterface $bookRepository;
    private MockInterface&AuthorRepositoryInterface $authorRepository;
    private MockInterface&GenreRepositoryInterface $genreRepository;
    private MockInterface&UpdateBookService $service;
    private UpdateBookController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookRepository = Mockery::mock(BookRepositoryInterface::class);
        $this->authorRepository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->genreRepository = Mockery::mock(GenreRepositoryInterface::class);
        $this->service = Mockery::mock(UpdateBookService::class);

        $this->app->instance(BookRepositoryInterface::class, $this->bookRepository);
        $this->app->instance(AuthorRepositoryInterface::class, $this->authorRepository);
        $this->app->instance(GenreRepositoryInterface::class, $this->genreRepository);
        $this->app->instance(UpdateBookService::class, $this->service);

        $this->controller = $this->app->make(UpdateBookController::class);
    }

    public function test_edit_returns_view(): void
    {
        $this->mockEditRepositories();

        $response = $this->controller->edit(1);

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_edit_returns_correct_view_name(): void
    {
        $this->mockEditRepositories();

        $response = $this->controller->edit(1);

        $this->assertSame('books.edit', $response->name());
    }

    public function test_edit_view_contains_book_authors_and_genres_keys(): void
    {
        $this->mockEditRepositories();

        $data = $this->controller->edit(1)->getData();

        $this->assertArrayHasKey('book', $data);
        $this->assertArrayHasKey('authors', $data);
        $this->assertArrayHasKey('genres', $data);
    }

    public function test_edit_view_contains_correct_book_data(): void
    {
        $dto = $this->makeBookResponseDto(id: 4, title: 'Dune');

        $this->bookRepository->shouldReceive('findByIdWithRelations')->andReturn($dto);
        $this->authorRepository->shouldReceive('getAll')->andReturn([]);
        $this->genreRepository->shouldReceive('getAll')->andReturn([]);

        $book = $this->controller->edit(4)->getData()['book'];

        $this->assertSame(4, $book->id);
        $this->assertSame('Dune', $book->title);
    }

    public function test_edit_calls_book_repository_find_by_id_with_correct_id(): void
    {
        $this->bookRepository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with(9)
            ->andReturn($this->makeBookResponseDto(id: 9, title: 'Book'));

        $this->authorRepository->shouldReceive('getAll')->andReturn([]);
        $this->genreRepository->shouldReceive('getAll')->andReturn([]);

        $this->controller->edit(9);
    }

    public function test_edit_calls_author_and_genre_repository_get_all(): void
    {
        $this->bookRepository->shouldReceive('findByIdWithRelations')->andReturn($this->makeBookResponseDto(id: 1, title: 'Book'));
        $this->authorRepository->shouldReceive('getAll')->once()->andReturn([]);
        $this->genreRepository->shouldReceive('getAll')->once()->andReturn([]);

        $this->controller->edit(1);
    }

    public function test_edit_passes_repository_data_to_view(): void
    {
        $book = $this->makeBookResponseDto(id: 1, title: 'Dune');
        $authors = [['id' => 1, 'name' => 'Frank Herbert']];
        $genres = [['id' => 2, 'name' => 'Sci-Fi']];

        $this->bookRepository->shouldReceive('findByIdWithRelations')->andReturn($book);
        $this->authorRepository->shouldReceive('getAll')->andReturn($authors);
        $this->genreRepository->shouldReceive('getAll')->andReturn($genres);

        $data = $this->controller->edit(1)->getData();

        $this->assertSame($book, $data['book']);
        $this->assertSame($authors, $data['authors']);
        $this->assertSame($genres, $data['genres']);
    }

    // --- update() ---

    public function test_update_calls_book_repository_get_by_id_with_correct_id(): void
    {
        $this->service->shouldReceive('execute')->once()->with(3, Mockery::any(), Mockery::any());

        $this->controller->update($this->makeRequest(), 3);
    }

    public function test_update_calls_service_execute_once(): void
    {
        $this->service->shouldReceive('execute')->once();

        $this->controller->update($this->makeRequest(), 1);
    }

    public function test_update_passes_correct_id_to_service(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with(7, Mockery::type(BookDto::class), Mockery::type('array'));

        $this->controller->update($this->makeRequest(), 7);
    }

    public function test_update_passes_correct_dto_to_service(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with(
                1,
                Mockery::on(fn (BookDto $dto) => $dto->title === 'New Title' && $dto->authorId === 2),
                Mockery::any(),
            );

        $this->controller->update($this->makeRequest(['title' => 'New Title', 'authorId' => 2]), 1);
    }

    public function test_update_passes_genres_from_request_to_service(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with(
                1,
                Mockery::type(BookDto::class),
                [3, 5],
            );

        $this->controller->update($this->makeRequest(['genres' => [3, 5]]), 1);
    }

    public function test_update_returns_redirect_response(): void
    {
        $this->service->shouldReceive('execute');

        $response = $this->controller->update($this->makeRequest(), 1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_update_redirects_to_books_index(): void
    {
        $this->service->shouldReceive('execute');

        $response = $this->controller->update($this->makeRequest(), 1);

        $this->assertSame(route('books.index'), $response->getTargetUrl());
    }

    private function mockEditRepositories(): void
    {
        $this->bookRepository->shouldReceive('findByIdWithRelations')->andReturn($this->makeBookResponseDto(id: 1, title: 'Book'));
        $this->authorRepository->shouldReceive('getAll')->andReturn([]);
        $this->genreRepository->shouldReceive('getAll')->andReturn([]);
    }

    private function makeRequest(array $overrides = []): BookWebDataRequest
    {
        return BookWebDataRequest::createFrom(
            Request::create('/books/1', 'PUT', array_merge($this->defaultData(), $overrides))
        );
    }

    private function defaultData(): array
    {
        return [
            'title' => 'Test Book',
            'slug' => 'test-book',
            'authorId' => 1,
            'description' => 'Test description',
            'price' => '19.99',
            'stock' => 10,
            'status' => 'active',
        ];
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
            price: '19.99',
            stock: 10,
            publishYear: null,
            coverImage: null,
            averageRating: 0.0,
            ratingsCount: 0,
            status: 'active',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
