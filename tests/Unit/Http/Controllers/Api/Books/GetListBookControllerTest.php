<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Books;

use App\Dto\Book\BookFiltersDto;
use App\Http\Controllers\Api\Books\GetListBookController;
use App\Http\Requests\Book\BookListRequest;
use App\Services\Book\SearchBookService;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListBookControllerTest extends TestCase
{
    use BookResponseDtoFactory;

    public function test_returns_200_with_list_of_books(): void
    {
        $books = [
            $this->makeBookResponseDto(id: 1, title: 'Book One'),
            $this->makeBookResponseDto(id: 2, title: 'Book Two'),
        ];

        /** @var SearchBookService&MockInterface $service */
        $service = $this->mock(SearchBookService::class);
        $service->shouldReceive('fetchList')->once()->andReturn($books);

        $request = BookListRequest::createFrom(Request::create('/api/books', 'GET'));
        $controller = new GetListBookController($service);
        $response = $controller->__invoke($request);

        $this->assertSame(200, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);

        $this->assertCount(2, $content['data']);
        $this->assertSame(1, $content['data'][0]['id']);
        $this->assertSame('Book One', $content['data'][0]['title']);
        $this->assertSame(2, $content['data'][1]['id']);
        $this->assertSame('Book Two', $content['data'][1]['title']);
    }

    public function test_returns_empty_collection_when_no_books_found(): void
    {
        /** @var SearchBookService&MockInterface $service */
        $service = $this->mock(SearchBookService::class);
        $service->shouldReceive('fetchList')->once()->andReturn([]);

        $request = BookListRequest::createFrom(Request::create('/api/books', 'GET'));
        $controller = new GetListBookController($service);
        $response = $controller->__invoke($request);

        $this->assertSame(200, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame([], $content['data']);
    }

    public function test_passes_default_filters_when_no_query_params(): void
    {
        /** @var SearchBookService&MockInterface $service */
        $service = $this->mock(SearchBookService::class);
        $service->shouldReceive('fetchList')
            ->once()
            ->withArgs(function (BookFiltersDto $dto): bool {
                return $dto->search === null
                    && $dto->perPage === 15
                    && $dto->sortBy === 'id'
                    && $dto->sortDirection === 'asc';
            })
            ->andReturn([]);

        $request = BookListRequest::createFrom(Request::create('/api/books', 'GET'));

        (new GetListBookController($service))->__invoke($request);
    }

    public function test_passes_search_query_to_service(): void
    {
        /** @var SearchBookService&MockInterface $service */
        $service = $this->mock(SearchBookService::class);
        $service->shouldReceive('fetchList')
            ->once()
            ->withArgs(fn(BookFiltersDto $dto): bool => $dto->search === 'Laravel')
            ->andReturn([]);

        $request = BookListRequest::createFrom(
            Request::create('/api/books', 'GET', ['search' => 'Laravel'])
        );

        (new GetListBookController($service))->__invoke($request);
    }

    public function test_passes_custom_pagination_params_to_service(): void
    {
        /** @var SearchBookService&MockInterface $service */
        $service = $this->mock(SearchBookService::class);
        $service->shouldReceive('fetchList')
            ->once()
            ->withArgs(function (BookFiltersDto $dto): bool {
                return $dto->perPage === 30
                    && $dto->sortBy === 'title'
                    && $dto->sortDirection === 'desc';
            })
            ->andReturn([]);

        $request = BookListRequest::createFrom(
            Request::create('/api/books', 'GET', [
                'perPage' => 30,
                'sortBy' => 'title',
                'sortDirection' => 'desc',
            ])
        );

        (new GetListBookController($service))->__invoke($request);
    }

    public function test_passes_all_filter_params_combined(): void
    {
        /** @var SearchBookService&MockInterface $service */
        $service = $this->mock(SearchBookService::class);
        $service->shouldReceive('fetchList')
            ->once()
            ->withArgs(function (BookFiltersDto $dto): bool {
                return $dto->search === 'PHP'
                    && $dto->perPage === 5
                    && $dto->sortBy === 'price'
                    && $dto->sortDirection === 'asc';
            })
            ->andReturn([]);

        $request = BookListRequest::createFrom(
            Request::create('/api/books', 'GET', [
                'search' => 'PHP',
                'perPage' => 5,
                'sortBy' => 'price',
                'sortDirection' => 'asc',
            ])
        );

        (new GetListBookController($service))->__invoke($request);
    }

    public function test_each_book_item_contains_expected_keys(): void
    {
        /** @var SearchBookService&MockInterface $service */
        $service = $this->mock(SearchBookService::class);
        $service->shouldReceive('fetchList')->andReturn([$this->makeBookResponseDto()]);

        $request  = BookListRequest::createFrom(Request::create('/api/books', 'GET'));
        $response = (new GetListBookController($service))->__invoke($request);

        $item = json_decode((string) $response->getContent(), true)['data'][0];

        foreach (['id', 'title', 'slug', 'authorId', 'price', 'stock', 'status'] as $key) {
            $this->assertArrayHasKey($key, $item, "Key '{$key}' missing from book item");
        }
    }
}
