<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Review;

use App\Services\Review\SearchReviewByQueryService;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class SearchReviewByQueryServiceTest extends TestCase
{
    private Client&MockInterface $client;
    private SearchReviewByQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(Client::class);
        $this->service = new SearchReviewByQueryService($this->client);
    }

    private function makeResponse(array $hits, int $total = 1): Elasticsearch&MockInterface
    {
        $responseData = ['hits' => ['hits' => $hits, 'total' => ['value' => $total]]];
        $response = Mockery::mock(Elasticsearch::class);
        $response->shouldReceive('asArray')->andReturn($responseData);
        return $response;
    }

    public function test_returns_ids(): void
    {
        $this->client->expects('search')->andReturn($this->makeResponse([['_id' => '5']], 1));

        $result = $this->service->search('great book');

        $this->assertSame([5], $result);
    }

    public function test_returns_empty_when_no_results(): void
    {
        $this->client->expects('search')->andReturn($this->makeResponse([], 0));

        $result = $this->service->search('nonexistent');

        $this->assertSame([], $result);
    }

    public function test_search_paginated_returns_ids_and_total(): void
    {
        $hits = [['_id' => '1'], ['_id' => '2']];
        $response = $this->makeResponse($hits, 10);
        $this->client->expects('search')->andReturn($response);

        $result = $this->service->searchPaginated('book', 2, 1);

        $this->assertSame([1, 2], $result[0]);
        $this->assertSame(10, $result[1]);
    }

    public function test_builds_bool_should_query(): void
    {
        $this->client->expects('search')->once()->andReturnUsing(function (array $params): Elasticsearch {
            $query = $params['body']['query'];
            $this->assertArrayHasKey('bool', $query);
            $this->assertArrayHasKey('should', $query['bool']);
            $shoulds = $query['bool']['should'];
            $this->assertCount(3, $shoulds);
            $this->assertArrayHasKey('match_phrase_prefix', $shoulds[0]);
            $this->assertArrayHasKey('user_name', $shoulds[0]['match_phrase_prefix']);
            $this->assertArrayHasKey('match_phrase_prefix', $shoulds[1]);
            $this->assertArrayHasKey('comment', $shoulds[1]['match_phrase_prefix']);
            $this->assertArrayHasKey('multi_match', $shoulds[2]);
            $this->assertContains('user_name^3', $shoulds[2]['multi_match']['fields']);
            $this->assertContains('comment', $shoulds[2]['multi_match']['fields']);

            return $this->makeResponse([]);
        });

        $this->service->search('test');
    }

    public function test_respects_limit(): void
    {
        $this->client->expects('search')->once()->andReturnUsing(function (array $params): Elasticsearch {
            $this->assertSame(10, $params['body']['size']);

            return $this->makeResponse([]);
        });

        $this->service->search('test', 10);
    }
}
