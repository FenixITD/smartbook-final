<?php

declare(strict_types=1);

namespace App\Services\Abstracts;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;

use function assert;
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

    /**
     * @return array<int, int>
     */
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

        assert($response instanceof Elasticsearch);

        return $this->extractIds($response);
    }

    /**
     * @return array{0: array<int, int>, 1: int}
     */
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

        assert($response instanceof Elasticsearch);

        $ids = $this->extractIds($response);

        /** @var array{hits: array{total: array{value: int}}} $data */
        $data = $response->asArray();
        $total = $data['hits']['total']['value'];

        return [$ids, $total];
    }

    /**
     * @return array<int, int>
     */
    protected function extractIds(Elasticsearch $response): array
    {
        /** @var array{hits: array{hits: array<int, array{_id: int|string}>}} $data */
        $data = $response->asArray();

        /** @var array<int, array{_id: int|string}> $hits */
        $hits = $data['hits']['hits'];

        return array_map(
            function (array $hit): int {
                /** @var int|string $id */
                $id = $hit['_id'];
                return (int) $id;
            },
            $hits,
        );
    }

    /**
     * @return array<string, mixed>
     */
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
