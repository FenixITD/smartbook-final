<?php

declare(strict_types=1);

namespace App\Services\Abstracts;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;

use function is_scalar;

abstract class AbstractSearchByQueryService
{
    public function __construct(protected Client $client)
    {
    }

    abstract protected function getIndexConfigKey(): string;

    protected function getIndexName(): string
    {
        $index = config($this->getIndexConfigKey());
        return is_scalar($index) ? (string) $index : '';
    }

    public function search(string $query, int $limit = 5): array
    {
        $response = $this->client->search([
            'index' => $this->getIndexName(),
            'body' => [
                'query' => $this->buildQueryArray($query),
                'size' => $limit,
                '_source' => false,
            ],
        ]);

        return $this->extractIds($response);
    }

    public function searchPaginated(string $query, int $perPage, int $page = 1): array
    {
        $from = ($page - 1) * $perPage;

        $response = $this->client->search([
            'index' => $this->getIndexName(),
            'body' => [
                'query' => $this->buildQueryArray($query),
                'size' => $perPage,
                'from' => $from,
                'track_total_hits' => true,
                '_source' => false,
            ],
        ]);

        $ids = $this->extractIds($response);
        $total = $response->asArray()['hits']['total']['value'] ?? 0;

        return [$ids, $total];
    }

    protected function extractIds(Elasticsearch|array $response): array
    {
        $data = $response instanceof Elasticsearch ? $response->asArray() : $response;
        $hits = $data['hits']['hits'] ?? [];

        return array_map(
            static fn (array $hit): int => is_numeric($hit['_id'] ?? null) ? (int) $hit['_id'] : 0,
            $hits,
        );
    }

    protected function buildQueryArray(string $query): array
    {
        return [
            'bool' => [
                'should' => [
                    [
                        'match_phrase_prefix' => [
                            $this->getSearchField() => [
                                'query' => $query,
                                'max_expansions' => 10,
                            ],
                        ],
                    ],
                    [
                        'multi_match' => [
                            'query' => $query,
                            'fields' => [$this->getSearchField().'^3'],
                            'fuzziness' => 'AUTO',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getSearchField(): string
    {
        return 'name';
    }
}
