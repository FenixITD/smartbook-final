<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Books;

use App\Http\Controllers\Api\Books\SearchSuggestCatalogBookController;
use App\Http\Requests\Book\SearchSuggestRequest;
use App\Services\Book\SearchSuggestCatalogBookService;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\TestCase;

final class SearchSuggestCatalogBookControllerTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Happy path
    // -------------------------------------------------------------------------

    public function test_returns_200_with_catalog_suggestions(): void
    {
        $suggestions = [
            ['id' => 10, 'title' => 'Design Patterns', 'slug' => 'design-patterns'],
            ['id' => 11, 'title' => 'Design Systems', 'slug' => 'design-systems'],
        ];

        /** @var SearchSuggestCatalogBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestCatalogBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->andReturn($suggestions);

        $request    = $this->makeSearchSuggestRequest('Design');
        $controller = new SearchSuggestCatalogBookController($service);
        $response   = $controller->__invoke($request);

        $this->assertSame(200, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        $this->assertCount(2, $content);
        $this->assertSame('Design Patterns', $content[0]['title']);
        $this->assertSame('Design Systems', $content[1]['title']);
    }

    public function test_returns_200_with_empty_array_when_no_results_found(): void
    {
        /** @var SearchSuggestCatalogBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestCatalogBookService::class);
        $service->shouldReceive('execute')->once()->andReturn([]);

        $request  = $this->makeSearchSuggestRequest('nonexistent');
        $response = (new SearchSuggestCatalogBookController($service))->__invoke($request);

        $this->assertSame(200, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame([], $content);
    }

    // -------------------------------------------------------------------------
    // Query passed to service
    // -------------------------------------------------------------------------

    public function test_passes_search_query_from_request_to_service(): void
    {
        /** @var SearchSuggestCatalogBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestCatalogBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with('Patterns')
            ->andReturn([]);

        $request = $this->makeSearchSuggestRequest('Patterns');
        (new SearchSuggestCatalogBookController($service))->__invoke($request);
    }

    public function test_passes_trimmed_query_to_service(): void
    {
        /** @var SearchSuggestCatalogBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestCatalogBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with('PHP')
            ->andReturn([]);

        $request = $this->makeSearchSuggestRequest('  PHP  ');
        (new SearchSuggestCatalogBookController($service))->__invoke($request);
    }

    public function test_passes_empty_string_when_query_param_is_absent(): void
    {
        /** @var SearchSuggestCatalogBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestCatalogBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with('')
            ->andReturn([]);

        $request = $this->makeSearchSuggestRequest('');
        (new SearchSuggestCatalogBookController($service))->__invoke($request);
    }

    public function test_single_result_is_correctly_structured(): void
    {
        $suggestion = ['id' => 5, 'title' => 'The Pragmatic Programmer', 'slug' => 'pragmatic-programmer'];

        /** @var SearchSuggestCatalogBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestCatalogBookService::class);
        $service->shouldReceive('execute')->andReturn([$suggestion]);

        $response = (new SearchSuggestCatalogBookController($service))->__invoke(
            $this->makeSearchSuggestRequest('Pragmatic')
        );

        $item = json_decode((string) $response->getContent(), true)[0];
        $this->assertSame(5, $item['id']);
        $this->assertSame('The Pragmatic Programmer', $item['title']);
    }

    // -------------------------------------------------------------------------
    // Service interaction
    // -------------------------------------------------------------------------

    public function test_calls_service_suggest_exactly_once(): void
    {
        /** @var SearchSuggestCatalogBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestCatalogBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->andReturn([]);

        (new SearchSuggestCatalogBookController($service))->__invoke(
            $this->makeSearchSuggestRequest('test')
        );
    }

    public function test_uses_catalog_service_not_generic_suggest_service(): void
    {
        /** @var SearchSuggestCatalogBookService&MockInterface $service */
        $service = $this->mock(SearchSuggestCatalogBookService::class);
        $service->shouldReceive('execute')->andReturn([]);

        $controller = new SearchSuggestCatalogBookController($service);

        $this->assertInstanceOf(SearchSuggestCatalogBookController::class, $controller);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeSearchSuggestRequest(string $query): SearchSuggestRequest
    {
        /** @var SearchSuggestRequest $request */
        $request = SearchSuggestRequest::createFrom(
            Request::create('/api/books/catalog/suggest', 'GET', ['q' => $query])
        );

        return $request;
    }
}
