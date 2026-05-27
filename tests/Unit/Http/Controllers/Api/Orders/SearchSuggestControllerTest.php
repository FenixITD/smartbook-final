<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Orders;

use App\Http\Controllers\Api\Orders\SearchSuggestController;
use App\Http\Requests\Book\SearchSuggestRequest;
use App\Services\Order\SearchSuggestOrderService;
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

        $this->service = Mockery::mock(SearchSuggestOrderService::class);
        $this->app->instance(SearchSuggestOrderService::class, $this->service);
        $this->controller = $this->app->make(SearchSuggestController::class);
    }

    public function test_returns_200_with_suggestions(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with('joh')
            ->andReturn([
                ['id' => 1, 'user_name' => 'John Doe', 'status' => 'pending', 'url' => 'http://example.test/orders/1'],
                ['id' => 2, 'user_name' => 'John Smith', 'status' => 'shipped', 'url' => 'http://example.test/orders/2'],
            ]);

        $response = ($this->controller)($this->makeRequest('joh'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_suggestions_array(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->andReturn([
                ['id' => 1, 'user_name' => 'John Doe', 'status' => 'pending', 'url' => 'http://example.test/orders/1'],
            ]);

        $response = ($this->controller)($this->makeRequest('joh'));
        $data = json_decode($response->getContent(), true);

        $this->assertCount(1, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame('John Doe', $data[0]['user_name']);
        $this->assertSame('pending', $data[0]['status']);
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
            ->with('john')
            ->andReturn([]);

        // SearchSuggestRequest::searchQuery() does trim() internally
        ($this->controller)($this->makeRequest('  john  '));
    }

    private function makeRequest(string $query): SearchSuggestRequest
    {
        return SearchSuggestRequest::createFrom(
            Request::create('/api/orders/suggest', 'GET', ['q' => $query])
        );
    }
}
