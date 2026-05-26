<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Books;

use App\Dto\Book\BookDto;
use App\Http\Controllers\Web\Books\CreateBookController;
use App\Http\Requests\Book\BookWebDataRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Book\CreateBookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateBookControllerTest extends TestCase
{
    private MockInterface&AuthorRepositoryInterface $authorRepository;
    private MockInterface&GenreRepositoryInterface $genreRepository;
    private MockInterface&CreateBookService $service;
    private CreateBookController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorRepository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->genreRepository = Mockery::mock(GenreRepositoryInterface::class);
        $this->service = Mockery::mock(CreateBookService::class);

        $this->app->instance(AuthorRepositoryInterface::class, $this->authorRepository);
        $this->app->instance(GenreRepositoryInterface::class, $this->genreRepository);
        $this->app->instance(CreateBookService::class, $this->service);

        $this->controller = $this->app->make(CreateBookController::class);
    }

    public function test_create_returns_view(): void
    {
        $this->authorRepository->shouldReceive('getAll')->andReturn([]);
        $this->genreRepository->shouldReceive('getAll')->andReturn([]);

        $response = $this->controller->create();

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_create_returns_correct_view_name(): void
    {
        $this->authorRepository->shouldReceive('getAll')->andReturn([]);
        $this->genreRepository->shouldReceive('getAll')->andReturn([]);

        $response = $this->controller->create();

        $this->assertSame('books.create', $response->name());
    }

    public function test_create_calls_author_repository_get_all(): void
    {
        $this->authorRepository->shouldReceive('getAll')->once()->andReturn([]);
        $this->genreRepository->shouldReceive('getAll')->andReturn([]);

        $this->controller->create();
    }

    public function test_create_calls_genre_repository_get_all(): void
    {
        $this->authorRepository->shouldReceive('getAll')->andReturn([]);
        $this->genreRepository->shouldReceive('getAll')->once()->andReturn([]);

        $this->controller->create();
    }

    public function test_create_view_contains_authors_and_genres_keys(): void
    {
        $this->authorRepository->shouldReceive('getAll')->andReturn([]);
        $this->genreRepository->shouldReceive('getAll')->andReturn([]);

        $data = $this->controller->create()->getData();

        $this->assertArrayHasKey('authors', $data);
        $this->assertArrayHasKey('genres', $data);
    }

    public function test_create_passes_repository_data_to_view(): void
    {
        $authors = [['id' => 1, 'name' => 'Tolkien']];
        $genres = [['id' => 2, 'name' => 'Fantasy']];

        $this->authorRepository->shouldReceive('getAll')->andReturn($authors);
        $this->genreRepository->shouldReceive('getAll')->andReturn($genres);

        $data = $this->controller->create()->getData();

        $this->assertSame($authors, $data['authors']);
        $this->assertSame($genres, $data['genres']);
    }

    public function test_store_calls_service_execute_once(): void
    {
        $this->service->shouldReceive('execute')->once();

        $this->controller->store($this->makeRequest());
    }

    public function test_store_passes_correct_dto_to_service(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn (BookDto $dto) => $dto->title === 'Dune' && $dto->authorId === 3),
                Mockery::any(),
            );

        $this->controller->store($this->makeRequest(['title' => 'Dune', 'authorId' => 3]));
    }

    public function test_store_passes_genres_from_request_to_service(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::type(BookDto::class),
                [1, 2, 3],
            );

        $this->controller->store($this->makeRequest(['genres' => [1, 2, 3]]));
    }

    public function test_store_returns_redirect_response(): void
    {
        $this->service->shouldReceive('execute');

        $response = $this->controller->store($this->makeRequest());

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_store_redirects_to_books_index(): void
    {
        $this->service->shouldReceive('execute');

        $response = $this->controller->store($this->makeRequest());

        $this->assertSame(route('books.index'), $response->getTargetUrl());
    }

    private function makeRequest(array $overrides = []): BookWebDataRequest
    {
        return BookWebDataRequest::createFrom(
            Request::create('/books', 'POST', array_merge($this->defaultData(), $overrides))
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
}
