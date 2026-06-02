<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Authors;

use App\Http\Controllers\Api\Authors\SearchSuggestController;
use App\Http\Requests\Book\SearchSuggestRequest;
use App\Services\Author\SearchSuggestAuthorService;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class SearchSuggestControllerTest extends TestCase
{
    private MockInterface $service;
    private SearchSuggestController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(SearchSuggestAuthorService::class);
        $this->app->instance(SearchSuggestAuthorService::class, $this->service);
        $this->controller = $this->app->make(SearchSuggestController::class);
    }

    public function test_returns_200_with_suggestions(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with('tol')
            ->andReturn([
                ['id' => 1, 'name' => 'Tolkien', 'url' => 'http://example.test/authors/1'],
                ['id' => 2, 'name' => 'Tolstoy', 'url' => 'http://example.test/authors/2'],
            ]);

        $response = ($this->controller)($this->makeRequest('tol'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_suggestions_array(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->andReturn([
                ['id' => 1, 'name' => 'Tolkien', 'url' => 'http://example.test/authors/1'],
            ]);

        $response = ($this->controller)($this->makeRequest('tol'));
        $data = json_decode($response->getContent(), true);

        $this->assertCount(1, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame('Tolkien', $data[0]['name']);
        $this->assertArrayHasKey('url', $data[0]);
    }

    public function test_returns_empty_array_when_no_suggestions(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->with('xyz')
            ->andReturn([]);

        $response = ($this->controller)($this->makeRequest('xyz'));
        $data = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $data);
    }

    public function test_passes_trimmed_query_from_request_to_service(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with('king')
            ->andReturn([]);

        // SearchSuggestRequest::searchQuery() does trim() internally
        ($this->controller)($this->makeRequest('  king  '));
    }

    private function makeRequest(string $query): SearchSuggestRequest
    {
        return SearchSuggestRequest::createFrom(
            Request::create('/api/authors/suggest', 'GET', ['q' => $query])
        );
    }
}
