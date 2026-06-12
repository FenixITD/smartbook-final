<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Books;

use App\Http\Controllers\Api\Books\SearchSuggestController;
use App\Http\Requests\Book\SearchSuggestRequest;
use App\Services\Book\SearchSuggestBookService;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\TestCase;

final class SearchSuggestControllerTest extends TestCase
{
    public function test_returns_200_with_suggestions(): void
    {
        $suggestions = [
            [
                'id' => 1,
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'cover_image' => null,
                'price' => 20.0,
                'url' => 'http://localhost/api/books/1'
            ],
            [
                'id' => 2,
                'title' => 'Clean Architecture',
                'author' => 'Robert C. Martin',
                'cover_image' => null,
                'price' => 25.0,
                'url' => 'http://localhost/api/books/2'
            ]
        ];

        /** @var SearchSuggestBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->andReturn($suggestions);

        $request = $this->makeSearchSuggestRequest('Clean');
        $controller = new SearchSuggestController($service);
        $response = $controller->__invoke($request);

        $this->assertSame(200, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        $this->assertCount(2, $content);
        $this->assertSame('Clean Code', $content[0]['title']);
        $this->assertSame('Clean Architecture', $content[1]['title']);
    }

    public function test_returns_200_with_empty_array_when_no_results(): void
    {
        /** @var SearchSuggestBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestBookService::class);
        $service->shouldReceive('execute')->once()->andReturn([]);

        $request = $this->makeSearchSuggestRequest('nonexistent');
        $response = (new SearchSuggestController($service))->__invoke($request);

        $this->assertSame(200, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame([], $content);
    }

    public function test_passes_search_query_from_request_to_service(): void
    {
        /** @var SearchSuggestBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with('Laravel')
            ->andReturn([]);

        $request = $this->makeSearchSuggestRequest('Laravel');
        (new SearchSuggestController($service))->__invoke($request);
    }

    public function test_passes_trimmed_query_to_service(): void
    {
        /** @var SearchSuggestBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with('PHP')
            ->andReturn([]);

        $request = $this->makeSearchSuggestRequest('  PHP  ');
        (new SearchSuggestController($service))->__invoke($request);
    }

    public function test_passes_empty_string_when_query_not_provided(): void
    {
        /** @var SearchSuggestBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with('')
            ->andReturn([]);

        $request = $this->makeSearchSuggestRequest('');
        (new SearchSuggestController($service))->__invoke($request);
    }

    public function test_calls_service_suggest_exactly_once(): void
    {
        /** @var SearchSuggestBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->andReturn([]);

        (new SearchSuggestController($service))->__invoke(
            $this->makeSearchSuggestRequest('test')
        );
    }

    private function makeSearchSuggestRequest(string $query): SearchSuggestRequest
    {
        /** @var SearchSuggestRequest $request */
        $request = SearchSuggestRequest::createFrom(
            Request::create('/api/books/suggest', 'GET', ['q' => $query])
        );

        return $request;
    }
}
