<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Genre;

use App\Services\Genre\SearchGenreByQueryService;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchGenreByQueryServiceTest extends TestCase
{
    private Client&MockInterface $client;
    private SearchGenreByQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(Client::class);
        $this->service = new SearchGenreByQueryService($this->client);
    }

    private function makeElasticsearchResponse(array $hits): Elasticsearch&MockInterface
    {
        $response = Mockery::mock(Elasticsearch::class);
        $response->expects('asArray')->andReturn(['hits' => ['hits' => $hits]]);
        return $response;
    }

    public function test_returns_array_of_ids(): void
    {
        $this->client->expects('search')->andReturn($this->makeElasticsearchResponse([['_id' => '10']]));

        $result = $this->service->search('query', 5);

        $this->assertSame([10], $result);
    }

    public function test_passes_limit_to_elasticsearch(): void
    {
        $this->client->expects('search')->withArgs(function (array $params): bool {
            return $params['body']['size'] === 15;
        })->andReturn($this->makeElasticsearchResponse([]));

        $this->service->search('query', 15);
    }
}
