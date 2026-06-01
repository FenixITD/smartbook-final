<?php

declare(strict_types=1);

namespace App\Services\Order;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;

class SearchOrderByQueryService
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @return array<int>
     * @throws ClientResponseException
     * @throws ServerResponseException
     *
     * Performs a full-text search for orders in Elasticsearch by the user's name, returning an array of matched order IDs
     */
    public function search(string $query, int $limit = 5): array
    {
        $index = config('elasticsearch.orders_index');

        /** @var Elasticsearch $response */
        $response = $this->client->search([
            'index' => is_scalar($index) ? (string) $index : '',
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'match_phrase_prefix' => [
                                    'user_name' => [
                                        'query' => $query,
                                        'max_expansions' => 10,
                                    ],
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $query,
                                    'fields' => ['user_name^2'],
                                    'fuzziness' => 'AUTO',
                                ],
                            ],
                        ],
                    ],
                ],
                'size' => $limit,
                '_source' => false,
            ],
        ]);

        /** @var array{hits: array{hits: array<int, array{_id: int|string}>}} $body */
        $body = $response->asArray();

        $hits = $body['hits']['hits'];

        return array_map(static fn (array $hit): int => (int) $hit['_id'], $hits);
    }
}
