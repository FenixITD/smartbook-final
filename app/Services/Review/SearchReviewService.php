<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Dto\Review\ReviewFiltersDto;
use App\Traits\ExecutesElasticsearchQueries;
use Elastic\Elasticsearch\Client;
use stdClass;

use function is_scalar;

class SearchReviewService
{
    use ExecutesElasticsearchQueries;

    public function __construct(private Client $client)
    {
    }

    /**
     * @return array{0: array<int>, 1: int}
     */
    public function search(ReviewFiltersDto $filters): array
    {
        $index = config('elasticsearch.genres_index');

        return $this->executeElasticsearchPaginatedQuery(
            $this->client,
            is_scalar($index) ? (string) $index : '',
            $this->buildQuery($filters),
            $filters->perPage
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildQuery(ReviewFiltersDto $filters): array
    {
        $must = [];
        $filter = [];

        if ($filters->search !== null && $filters->search !== '') {
            $must[] = [
                'multi_match' => [
                    'query' => $filters->search,
                    'fields' => ['user_name^3', 'comment'],
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
