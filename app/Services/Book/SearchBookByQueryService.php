<?php

declare(strict_types=1);

namespace App\Services\Book;

use Elastic\Elasticsearch\Client;

final readonly class SearchBookByQueryService
{
    public function __construct(private Client $client)
    {
    }

    /** @return array<int> */
    public function search(string $query, int $limit = 10000): array
    {
        $response = $this->client->search([
            'index' => config('elasticsearch.books_index'),
            'body' => [
                'query' => [
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
                ],
                'size' => $limit,
                '_source' => false,
            ],
        ]);

        return array_map(
            static fn (array $hit): int => (int) $hit['_id'],
            $response['hits']['hits'],
        );
    }
}
