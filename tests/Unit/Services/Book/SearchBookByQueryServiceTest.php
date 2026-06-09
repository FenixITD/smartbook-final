<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Book;

use App\Services\Book\SearchBookByQueryService;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchBookByQueryServiceTest extends TestCase
{
    private Client&MockInterface $client;
    private SearchBookByQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(Client::class);
        $this->service = new SearchBookByQueryService($this->client);
    }

    private function makeElasticsearchResponse(array $hits): Elasticsearch&MockInterface
    {
        $response = Mockery::mock(Elasticsearch::class);
        $response->expects('asArray')->andReturn(['hits' => ['hits' => $hits]]);
        return $response;
    }

    public function test_returns_array_of_ids(): void
    {
        $this->client->expects('search')->andReturn($this->makeElasticsearchResponse([['_id' => '1'], ['_id' => '2']]));

        $result = $this->service->search('query');

        $this->assertSame([1, 2], $result);
    }
}
