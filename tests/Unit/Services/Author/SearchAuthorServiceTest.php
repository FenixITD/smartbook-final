<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Author;

use App\Dto\Author\AuthorFiltersDto;
use App\Services\Author\SearchAuthorService;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchAuthorServiceTest extends TestCase
{
    private Client&MockInterface $client;
    private SearchAuthorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Mockery::mock(Client::class);
        $this->service = new SearchAuthorService($this->client);
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

    public function test_returns_array_of_ids(): void
    {
        $hits = [
            ['_id' => '10'],
            ['_id' => '20'],
        ];

        $this->client
            ->expects('search')
            ->andReturn($this->makeElasticsearchResponse($hits));

        $filters = new AuthorFiltersDto(search: 'Pushkin');
        $result = $this->service->search($filters);

        $this->assertSame([10, 20], $result);
    }

    public function test_returns_empty_array_when_no_hits(): void
    {
        $this->client
            ->expects('search')
            ->andReturn($this->makeElasticsearchResponse([]));

        $filters = new AuthorFiltersDto(search: 'unknown');
        $result = $this->service->search($filters);

        $this->assertSame([], $result);
    }

    public function test_ids_are_cast_to_int(): void
    {
        $this->client
            ->expects('search')
            ->andReturn($this->makeElasticsearchResponse([['_id' => '7']]));

        $filters = new AuthorFiltersDto(search: 'test');
        $result = $this->service->search($filters);

        $this->assertIsInt($result[0]);
        $this->assertSame(7, $result[0]);
    }

    public function test_uses_match_all_when_search_is_null(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                return isset($params['body']['query']['match_all']);
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $filters = new AuthorFiltersDto(search: null);
        $this->service->search($filters);
    }

    public function test_uses_match_all_when_search_is_empty_string(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                return isset($params['body']['query']['match_all']);
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $filters = new AuthorFiltersDto(search: '');
        $this->service->search($filters);
    }

    public function test_uses_multi_match_query_when_search_is_provided(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                $query = $params['body']['query'];

                return isset($query['bool']['must'][0]['multi_match']['query'])
                    && $query['bool']['must'][0]['multi_match']['query'] === 'Chekhov';
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $filters = new AuthorFiltersDto(search: 'Chekhov');
        $this->service->search($filters);
    }

    public function test_multi_match_searches_name_field_with_boost(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                $fields = $params['body']['query']['bool']['must'][0]['multi_match']['fields'] ?? [];

                return in_array('name^3', $fields, true);
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $filters = new AuthorFiltersDto(search: 'Chekhov');
        $this->service->search($filters);
    }

    public function test_passes_correct_index(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                return $params['index'] === 'authors';
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $filters = new AuthorFiltersDto();
        $this->service->search($filters);
    }

    public function test_size_is_10000(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                return $params['body']['size'] === 10000;
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $filters = new AuthorFiltersDto();
        $this->service->search($filters);
    }

    public function test_source_is_excluded(): void
    {
        $this->client
            ->expects('search')
            ->withArgs(function (array $params): bool {
                return $params['body']['_source'] === false;
            })
            ->andReturn($this->makeElasticsearchResponse([]));

        $filters = new AuthorFiltersDto();
        $this->service->search($filters);
    }
}
