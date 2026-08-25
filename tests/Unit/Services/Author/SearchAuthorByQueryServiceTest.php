<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Author;

use App\Services\Author\SearchAuthorByQueryService;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchAuthorByQueryServiceTest extends TestCase
{
    private Client&MockInterface $client;
    private SearchAuthorByQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Mockery::mock(Client::class);
        $this->service = new SearchAuthorByQueryService($this->client);
    }

    private function makeElasticsearchResponse(array $hits): Elasticsearch&MockInterface
    {
        $response = Mockery::mock(Elasticsearch::class);
        $response->expects('asArray')->andReturn([
            'hits' => [
                'hits' => $hits,
            ],
        ]);

        return $response;
    }

    public function test_returns_array_of_ids_from_elasticsearch(): void
    {
        $hits = [
            ['_id' => '1'],
            ['_id' => '2'],
            ['_id' => '3'],
        ];

        $this->client
            ->expects('search')
            ->andReturn($this->makeElasticsearchResponse($hits));

        $result = $this->service->search('Tolstoy');

        $this->assertSame([1, 2, 3], $result);
    }

    public function test_returns_empty_array_when_no_hits(): void
    {
        $this->client
            ->expects('search')
            ->andReturn($this->makeElasticsearchResponse([]));

        $result = $this->service->search('unknown author');

        $this->assertSame([], $result);
    }

    public function test_ids_are_cast_to_int(): void
    {
        $hits = [
            ['_id' => '42'],
        ];

        $this->client
            ->expects('search')
            ->andReturn($this->makeElasticsearchResponse($hits));

        $result = $this->service->search('query');

        $this->assertSame([42], $result);
        $this->assertIsInt($result[0]);
    }

    public function test_passes_correct_index_to_elasticsearch(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                return $params['index'] === 'authors';
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $this->service->search('query');
    }

    public function test_passes_correct_limit_to_elasticsearch(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                return $params['body']['size'] === 10;
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $this->service->search('query', limit: 10);
    }

    public function test_default_limit_is_five(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                return $params['body']['size'] === 5;
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $this->service->search('query');
    }

    public function test_query_body_contains_match_phrase_prefix_and_multi_match(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                $should = $params['body']['query']['bool']['should'];

                $hasMatchPhrase = isset($should[0]['match_phrase_prefix']['name']['query']);
                $hasMultiMatch = isset($should[1]['multi_match']['query']);

                return $hasMatchPhrase && $hasMultiMatch;
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $this->service->search('Dostoevsky');
    }

    public function test_source_is_excluded_from_response(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                return $params['body']['_source'] === false;
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $this->service->search('query');
    }
}
