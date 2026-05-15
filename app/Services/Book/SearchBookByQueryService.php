<?php

declare(strict_types=1);

namespace App\Services\Book;

use Elastic\Elasticsearch\Client;

final readonly class SearchBookByQueryService
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @param string $query
     * @param int $limit
     * @return array<int>
     *
     * Performs a full-text search for books in Elasticsearch by title and description, returning an array of matched book IDs.
     */
    public function search(string $query, int $limit = 10000): array
    {
        $response = $this->client->search([
            'index' => (string) config('elasticsearch.books_index'),
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

        /** @var array<int, array<string, mixed>> $hits */
        $hits = $response->asArray()['hits']['hits'];

        return array_map(
            static fn (array $hit): int => (int) $hit['_id'],
            $hits,
        );
    }
}
