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

    private function makeElasticsearchResponse(array $hits, int $total = 1): Elasticsearch&MockInterface
    {
        $response = Mockery::mock(Elasticsearch::class);
        $response->expects('asArray')->andReturn(['hits' => ['hits' => $hits, 'total' => ['value' => $total]]]);

        return $response;
    }

    public function test_returns_ids_and_total(): void
    {
        $this->client->expects('search')->andReturn($this->makeElasticsearchResponse([['_id' => '3']], 1));

        $result = $this->service->search(new DashboardFiltersDto);

        $this->assertSame([3], $result[0]);
        $this->assertSame(1, $result[1]);
    }

    public function test_returns_empty_when_no_results(): void
    {
        $this->client->expects('search')->andReturn($this->makeElasticsearchResponse([], 0));

        $result = $this->service->search(new DashboardFiltersDto);

        $this->assertSame([], $result[0]);
        $this->assertSame(0, $result[1]);
    }

    public function test_returns_multiple_ids(): void
    {
        $hits = [['_id' => '10'], ['_id' => '20'], ['_id' => '30']];
        $this->client->expects('search')->andReturn($this->makeElasticsearchResponse($hits, 3));

        $result = $this->service->search(new DashboardFiltersDto);

        $this->assertSame([10, 20, 30], $result[0]);
        $this->assertSame(3, $result[1]);
    }

    public function test_filters_active_status_for_customers(): void
    {
        $this->client->expects('search')
            ->with(Mockery::on(function (array $params): bool {
                $filter = $params['body']['query']['bool']['filter'] ?? [];

                return in_array(['term' => ['status' => 'active']], $filter, true);
            }))
            ->andReturn($this->makeElasticsearchResponse([['_id' => '1']], 1));

        $this->service->search(new DashboardFiltersDto);
    }

    public function test_includes_all_statuses_for_admin(): void
    {
        $this->client->expects('search')
            ->with(Mockery::on(function (array $params): bool {
                return isset($params['body']['query']['match_all']);
            }))
            ->andReturn($this->makeElasticsearchResponse([['_id' => '1'], ['_id' => '2']], 2));

        $this->service->search(new DashboardFiltersDto(showNonActive: true));
    }
}
