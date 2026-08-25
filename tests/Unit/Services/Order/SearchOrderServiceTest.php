<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Order;

use App\Dto\Order\OrderFiltersDto;
use App\Services\Order\SearchOrderService;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchOrderServiceTest extends TestCase
{
    private Client&MockInterface $client;
    private SearchOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(Client::class);
        $this->service = new SearchOrderService($this->client);
    }

    private function makeElasticsearchResponse(array $hits, int $total = 0): Elasticsearch&MockInterface
    {
        $response = Mockery::mock(Elasticsearch::class);
        $response->expects('asArray')->andReturn([
            'hits' => [
                'total' => ['value' => $total],
                'hits' => $hits,
            ],
        ]);
        return $response;
    }

    public function test_returns_ids_and_total(): void
    {
        $hits = [['_id' => '1'], ['_id' => '2']];
        $this->client->expects('search')->andReturn($this->makeElasticsearchResponse($hits, 2));

        [$ids, $total] = $this->service->search(new OrderFiltersDto());

        $this->assertSame([1, 2], $ids);
        $this->assertSame(2, $total);
    }

    public function test_uses_match_all_when_search_and_id_are_null(): void
    {
        $this->client->expects('search')->withArgs(function (array $params): bool {
            return isset($params['body']['query']['match_all']);
        })->andReturn($this->makeElasticsearchResponse([]));

        $this->service->search(new OrderFiltersDto());
    }

    public function test_uses_multi_match_when_search_is_provided(): void
    {
        $this->client->expects('search')->withArgs(function (array $params): bool {
            $query = $params['body']['query'];
            return isset($query['bool']['must'][0]['multi_match']['query'])
                && $query['bool']['must'][0]['multi_match']['query'] === 'john';
        })->andReturn($this->makeElasticsearchResponse([]));

        $this->service->search(new OrderFiltersDto(search: 'john'));
    }

    public function test_uses_term_when_id_is_provided(): void
    {
        $this->client->expects('search')->withArgs(function (array $params): bool {
            $query = $params['body']['query'];
            return isset($query['bool']['filter'][0]['term']['id'])
                && $query['bool']['filter'][0]['term']['id'] === 5;
        })->andReturn($this->makeElasticsearchResponse([]));

        $this->service->search(new OrderFiltersDto(id: 5));
    }
}
