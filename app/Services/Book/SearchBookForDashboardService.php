<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Dto\Dashboard\DashboardFiltersDto;
use Elastic\Elasticsearch\Client;
use stdClass;

final readonly class SearchBookForDashboardService
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @param DashboardFiltersDto $filters
     * @return array<int>
     *
     * Searches and filters books in Elasticsearch specifically for the admin dashboard, returning an array of book IDs.
     */
    public function search(DashboardFiltersDto $filters): array
    {
        $response = $this->client->search([
            'index' => (string) config('elasticsearch.books_index'),
            'body' => [
                'query' => $this->buildQuery($filters),
                'sort' => $this->buildSort($filters->sort),
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

    /** @return array<string, mixed> */
    private function buildQuery(DashboardFiltersDto $filters): array
    {
        $must = [];
        $filter = [];

        if ($filters->search !== null && $filters->search !== '') {
            $must[] = [
                'multi_match' => [
                    'query' => $filters->search,
                    'fields' => ['title^3', 'description'],
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO',
                ],
            ];
        }

        if ($filters->genre !== null) {
            $filter[] = ['term' => ['genre_ids' => $filters->genre]];
        }

        if ($filters->author !== null) {
            $filter[] = ['term' => ['author_id' => $filters->author]];
        }

        if ($filters->year !== null) {
            $filter[] = ['term' => ['publish_year' => $filters->year]];
        }

        if ($filters->status !== null) {
            $filter[] = ['term' => ['status' => $filters->status]];
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

    /**
     * @return array<int, array<string, array<string, string>>>
     */
    private function buildSort(string $sort): array
    {
        return match ($sort) {
            'price_asc' => [['price' => ['order' => 'asc']]],
            'price_desc' => [['price' => ['order' => 'desc']]],
            'newest' => [['publish_year' => ['order' => 'desc']]],
            default => [['average_rating' => ['order' => 'desc']]],
        };
    }
}
