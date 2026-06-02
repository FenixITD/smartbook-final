<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Reviews;

use App\Http\Controllers\Api\Reviews\SearchSuggestController;
use App\Http\Requests\Book\SearchSuggestRequest;
use App\Services\Review\SearchSuggestReviewService;
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

        $this->service = Mockery::mock(SearchSuggestReviewService::class);
        $this->app->instance(SearchSuggestReviewService::class, $this->service);
        $this->controller = $this->app->make(SearchSuggestController::class);
    }

    public function test_returns_200_with_suggestions(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with('great')
            ->andReturn([
                ['id' => 1, 'user_name' => 'Alice', 'content' => 'Great book indeed...', 'url' => 'http://example.test/reviews/1'],
                ['id' => 2, 'user_name' => 'Bob', 'content' => 'Greatest novel ever...', 'url' => 'http://example.test/reviews/2'],
            ]);

        $response = ($this->controller)($this->makeRequest('great'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_suggestions_array(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->andReturn([
                ['id' => 1, 'user_name' => 'Alice', 'content' => 'Great book indeed...', 'url' => 'http://example.test/reviews/1'],
            ]);

        $response = ($this->controller)($this->makeRequest('great'));
        $data = json_decode($response->getContent(), true);

        $this->assertCount(1, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame('Alice', $data[0]['user_name']);
        $this->assertArrayHasKey('content', $data[0]);
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
            ->with('great')
            ->andReturn([]);

        // SearchSuggestRequest::searchQuery() does trim() internally
        ($this->controller)($this->makeRequest('  great  '));
    }

    private function makeRequest(string $query): SearchSuggestRequest
    {
        return SearchSuggestRequest::createFrom(
            Request::create('/api/reviews/suggest', 'GET', ['q' => $query])
        );
    }
}
