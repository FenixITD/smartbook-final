<?php

declare(strict_types=1);

namespace App\Services\Abstracts;

use App\Traits\ExecutesElasticsearchQueries;
use Elastic\Elasticsearch\Client;
use stdClass;

use function is_scalar;

abstract class AbstractSearchService
{
    use ExecutesElasticsearchQueries;

    public function __construct(protected Client $client)
    {
    }

    abstract protected function getIndexConfigKey(): string;

    /**
     * @param object{search: string|null, perPage: int} $filters
     * @return array<int, mixed>
     */
    public function search(mixed $filters): array
    {
        $index = config($this->getIndexConfigKey());
        $indexStr = is_scalar($index) ? (string) $index : '';

        return $this->executeElasticsearchPaginatedQuery(
            $this->client,
            $indexStr,
            $this->buildQuery($filters),
            $filters->perPage
        );
    }

    /**
     * @param object{search: string|null, perPage: int} $filters
     * @return array<string, mixed>
     */
    protected function buildQuery(mixed $filters): array
    {
        if ($filters->search === null || $filters->search === '') {
            return ['match_all' => new stdClass()];
        }

        return [
            'bool' => [
                'must' => [[
                    'multi_match' => [
                        'query' => $filters->search,
                        'fields' => $this->getSearchFields(),
                        'fuzziness' => 'AUTO',
                    ],
                ]],
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function getSearchFields(): array
    {
        return ['name^3'];
    }
}
