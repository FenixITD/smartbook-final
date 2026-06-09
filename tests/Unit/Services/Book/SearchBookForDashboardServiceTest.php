<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Book;

use App\Dto\Dashboard\DashboardFiltersDto;
use App\Services\Book\SearchBookForDashboardService;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchBookForDashboardServiceTest extends TestCase
{
    private Client&MockInterface $client;
    private SearchBookForDashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(Client::class);
        $this->service = new SearchBookForDashboardService($this->client);
    }

    private function makeElasticsearchResponse(array $hits): Elasticsearch&MockInterface
    {
        $response = Mockery::mock(Elasticsearch::class);
        $response->expects('asArray')->andReturn(['hits' => ['hits' => $hits]]);
        return $response;
    }

    public function test_returns_array_of_ids(): void
    {
        $this->client->expects('search')->andReturn($this->makeElasticsearchResponse([['_id' => '3']]));

        $result = $this->service->search(new DashboardFiltersDto());

        $this->assertSame([3], $result);
    }
}
