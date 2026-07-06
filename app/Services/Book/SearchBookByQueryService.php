<?php

declare(strict_types=1);

namespace App\Services\Book;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;

use function is_scalar;

class SearchBookByQueryService
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @return array<int>
     */
    public function search(string $query, int $limit = 5): array
    {
        $index = config('elasticsearch.books_index');

        /** @var Elasticsearch $response */
        $response = $this->client->search([
            'index' => is_scalar($index) ? (string) $index : '',
            'body' => [
                'query' => $this->buildQueryArray($query),
                'size' => $limit,
                '_source' => false,
            ],
        ]);

        /** @var array{hits: array{hits: array<int, array<string, mixed>>}} $data */
        $data = $response->asArray();
        $hits = $data['hits']['hits'];

        return array_map(
            static fn (array $hit): int => is_numeric($hit['_id'] ?? null) ? (int) $hit['_id'] : 0,
            $hits,
        );
    }

    /**
     * @return array{0: array<int>, 1: int}
     */
    public function searchPaginated(string $query, int $perPage, int $page = 1): array
    {
        $index = config('elasticsearch.books_index');
        $from = ($page - 1) * $perPage;

        /** @var Elasticsearch $response */
        $response = $this->client->search([
            'index' => is_scalar($index) ? (string) $index : '',
            'body' => [
                'query' => $this->buildQueryArray($query),
                'size' => $perPage,
                'from' => $from,
                'track_total_hits' => true,
                '_source' => false,
            ],
        ]);

        /** @var array{hits: array{total: array{value: int}, hits: array<int, array<string, mixed>>}} $data */
        $data = $response->asArray();
        $hits = $data['hits']['hits'];
        $total = $data['hits']['total']['value'] ?? 0;

        $ids = array_map(
            static fn (array $hit): int => is_numeric($hit['_id'] ?? null) ? (int) $hit['_id'] : 0,
            $hits,
        );

        return [$ids, $total];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildQueryArray(string $query): array
    {
        return [
            'bool' => [
                'should' => [
                    [
                        'match_phrase_prefix' => [
                            'title' => [
                                'query' => $query,
                                'boost' => 3,
                            ],
                        ],
                    ],
                    [
                        'multi_match' => [
                            'query' => $query,
                            'fields' => ['title^3', 'description'],
                            'type' => 'best_fields',
                            'fuzziness' => 'AUTO',
                        ],
                    ],
                ],
                'minimum_should_match' => 1,
            ],
        ];
    }
}
