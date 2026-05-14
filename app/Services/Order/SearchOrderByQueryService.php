<?php

declare(strict_types=1);

namespace App\Services\Order;

use Elastic\Elasticsearch\Client;

final readonly class SearchOrderByQueryService
{
    public function __construct(private Client $client)
    {
    }

    /** @return array<int> */
    public function search(string $query, int $limit = 5): array
    {
        $response = $this->client->search([
            'index' => (string) config('elasticsearch.orders_index'),
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

        /** @var array<int, array<string, mixed>> $hits */
        $hits = $response->asArray()['hits']['hits'];

        return array_map(static fn (array $hit): int => (int) $hit['_id'], $hits);
    }
}
