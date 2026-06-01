<?php

declare(strict_types=1);

namespace App\Services\Review;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;

final readonly class SearchReviewByQueryService
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @return array<int>
     * @throws ClientResponseException
     * @throws ServerResponseException
     *
     * Performs a full-text search for reviews in Elasticsearch by user name and comment content, returning an array of matched review IDs
     */
    public function search(string $query, int $limit = 5): array
    {
        $index = config('elasticsearch.reviews_index');

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
                                'match_phrase_prefix' => [
                                    'comment' => [
                                        'query' => $query,
                                        'max_expansions' => 10,
                                    ],
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $query,
                                    'fields' => ['user_name^3', 'comment'],
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
