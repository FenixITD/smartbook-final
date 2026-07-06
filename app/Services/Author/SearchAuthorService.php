<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\Dto\Author\AuthorFiltersDto;
use App\Traits\ExecutesElasticsearchQueries;
use Elastic\Elasticsearch\Client;
use stdClass;

use function is_scalar;

class SearchAuthorService
{
    use ExecutesElasticsearchQueries;

    public function __construct(private Client $client)
    {
    }

    /**
     * @return array{0: array<int>, 1: int}
     */
    public function search(AuthorFiltersDto $filters): array
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
    private function buildQuery(AuthorFiltersDto $filters): array
    {
        if ($filters->search === null || $filters->search === '') {
            return ['match_all' => new stdClass()];
        }

        return [
            'bool' => [
                'must' => [[
                    'multi_match' => [
                        'query' => $filters->search,
                        'fields' => ['name^3'],
                        'fuzziness' => 'AUTO',
                    ],
                ]],
            ],
        ];
    }
}
