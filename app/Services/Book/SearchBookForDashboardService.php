<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Dto\Dashboard\DashboardFiltersDto;
use App\Traits\ExecutesElasticsearchQueries;
use Elastic\Elasticsearch\Client;
use stdClass;

class SearchBookForDashboardService
{
    use ExecutesElasticsearchQueries;

    public function __construct(private Client $client)
    {
    }

    /**
     * @return array{0: array<int>, 1: int}
     */
    public function search(DashboardFiltersDto $filters): array
    {
        $index = config('elasticsearch.books_index');

        return $this->executeElasticsearchPaginatedQuery(
            $this->client,
            is_scalar($index) ? (string) $index : '',
            $this->buildQuery($filters),
            $filters->perPage,
            $this->buildSort($filters->sort)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildQuery(DashboardFiltersDto $filters): array
    {
        $must = [];
        $filter = [];

        if ($filters->search !== null && $filters->search !== '') {
            $must[] = [
                'bool' => [
                    'should' => [
                        [
                            'match_phrase_prefix' => [
                                'title' => [
                                    'query' => $filters->search,
                                    'boost' => 3,
                                ],
                            ],
                        ],
                        [
                            'multi_match' => [
                                'query' => $filters->search,
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
     * @return array<int, mixed>
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
