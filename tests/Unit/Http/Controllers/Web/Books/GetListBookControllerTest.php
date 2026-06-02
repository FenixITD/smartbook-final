<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Books;

use App\Dto\Book\BookFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Http\Controllers\Web\Books\GetListBookController;
use App\Http\Requests\Book\BookListRequest;
use App\Services\Book\SearchBookService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListBookControllerTest extends TestCase
{
    private MockInterface&SearchBookService $searchService;
    private GetListBookController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchService = Mockery::mock(SearchBookService::class);
        $this->app->instance(SearchBookService::class, $this->searchService);
        $this->controller = $this->app->make(GetListBookController::class);
    }

    public function test_returns_view(): void
    {
        $this->searchService
            ->shouldReceive('getWebList')
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)($this->makeRequest());

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_returns_correct_view_name(): void
    {
        $this->searchService
            ->shouldReceive('getWebList')
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame('books.list', $response->name());
    }

    public function test_view_contains_books_key(): void
    {
        $this->searchService
            ->shouldReceive('getWebList')
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)($this->makeRequest());

        $this->assertArrayHasKey('books', $response->getData());
    }

    public function test_view_contains_books_data_from_service(): void
    {
        $books = Mockery::mock(PaginatedResponseDto::class);

        $this->searchService
            ->shouldReceive('getWebList')
            ->andReturn($books);

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame($books, $response->getData()['books']);
    }

    public function test_calls_service_once(): void
    {
        $this->searchService
            ->shouldReceive('getWebList')
            ->once()
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($this->makeRequest());
    }

    public function test_passes_filters_dto_from_request_to_service(): void
    {
        $this->searchService
            ->shouldReceive('getWebList')
            ->once()
            ->with(Mockery::on(function (BookFiltersDto $dto) {
                return $dto->search === 'dune'
                    && $dto->perPage === 10
                    && $dto->sortBy === 'title'
                    && $dto->sortDirection === 'desc';
            }))
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($this->makeRequest([
            'search' => 'dune',
            'perPage' => 10,
            'sortBy' => 'title',
            'sortDirection' => 'desc',
        ]));
    }

    public function test_uses_default_filters_when_no_query_params(): void
    {
        $this->searchService
            ->shouldReceive('getWebList')
            ->once()
            ->with(Mockery::on(function (BookFiltersDto $dto) {
                return $dto->search === null
                    && $dto->perPage === 15
                    && $dto->sortBy === 'id'
                    && $dto->sortDirection === 'asc';
            }))
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($this->makeRequest());
    }

    private function makeRequest(array $params = []): BookListRequest
    {
        return BookListRequest::createFrom(
            Request::create('/books', 'GET', $params)
        );
    }
}
