<?php

declare(strict_types=1);

namespace App\Services\Genre;

use App\Dto\Genre\GenreFiltersDto;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use stdClass;

use function is_scalar;

class SearchGenreService
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     *
     * @return array<int>
     */
    public function search(GenreFiltersDto $filters): array
    {
        $index = config('elasticsearch.genres_index');

        /** @var Elasticsearch $response */
        $response = $this->client->search([
            'index' => is_scalar($index) ? (string) $index : '',
            'body' => [
                'query' => $this->buildQuery($filters),
                'size' => 10000,
                '_source' => false,
            ],
        ]);

        /** @var array{hits: array{hits: array<int, array{_id: int|string}>}} $body */
        $body = $response->asArray();

        $hits = $body['hits']['hits'];

        return array_map(static fn (array $hit): int => (int) $hit['_id'], $hits);
    }

    /**
     * @return array<string, mixed>
     *
     * Builds the Elasticsearch query array based on the provided genre filters
     */
    private function buildQuery(GenreFiltersDto $filters): array
    {
        if ($filters->search === null || $filters->search === '') {
            return ['match_all' => new stdClass()];
        }

        return [
            'bool' => [
                'must' => [[
                    'multi_match' => [
                        'query' => $filters->search,
                        'fields' => ['name^3'],
                        'fuzziness' => 'AUTO',
                    ],
                ]],
            ],
        ];
    }
}
