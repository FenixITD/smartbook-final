<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Dto\Review\ReviewFiltersDto;
use Elastic\Elasticsearch\Client;
use stdClass;

final readonly class SearchReviewService
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @param ReviewFiltersDto $filters
     * @return array<int>
     *
     * Searches and filters reviews in Elasticsearch based on provided criteria, returning an array of review IDs.
     */
    public function search(ReviewFiltersDto $filters): array
    {
        $response = $this->client->search([
            'index' => (string) config('elasticsearch.reviews_index'),
            'body' => [
                'query' => $this->buildQuery($filters),
                'size' => 10000,
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

    /**
     * @param ReviewFiltersDto $filters
     * @return array<string, mixed>
     *
     * Builds the Elasticsearch query array based on the provided review filters.
     */
    private function buildQuery(ReviewFiltersDto $filters): array
    {
        $must = [];
        $filter = [];

        if ($filters->search !== null && $filters->search !== '') {
            $must[] = [
                'multi_match' => [
                    'query' => $filters->search,
                    'fields' => ['content'],
                    'fuzziness' => 'AUTO',
                ],
            ];
        }

        if ($filters->id !== null) {
            $filter[] = ['term' => ['id' => $filters->id]];
        }

        if ($must === [] && $filter === []) {
            return ['match_all' => new stdClass()];
        }

        $bool = [];

        if ($must !== []) {
            $bool['must'] = $must;
        }

        if ($filter !== []) {
            $bool['filter'] = $filter;
        }

        return ['bool' => $bool];
    }
}
