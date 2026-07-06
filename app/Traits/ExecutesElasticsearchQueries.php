<?php

declare(strict_types=1);

namespace App\Traits;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Illuminate\Pagination\Paginator;

trait ExecutesElasticsearchQueries
{
    /**
     * @param array<string, mixed> $query
     * @param array<int, mixed>|null $sort
     *
     * @return array{0: array<int>, 1: int}
     */
    protected function executeElasticsearchPaginatedQuery(
        Client $client,
        string $index,
        array $query,
        int $perPage,
        ?array $sort = null
    ): array {
        $page = Paginator::resolveCurrentPage() ?: 1;
        $from = ($page - 1) * $perPage;

        $body = [
            'query' => $query,
            'size' => $perPage,
            'from' => $from,
            'track_total_hits' => true,
            '_source' => false,
        ];

        if ($sort !== null) {
            $body['sort'] = $sort;
        }

        /** @var Elasticsearch $response */
        $response = $client->search([
            'index' => $index,
            'body' => $body,
        ]);

        /** @var array{hits: array{total: array{value: int}, hits: array<int, array<string, mixed>>}} $data */
        $data = $response->asArray();
        $hits = $data['hits']['hits'] ?? [];
        $total = $data['hits']['total']['value'] ?? 0;

        $ids = array_map(
            static fn (array $hit): int => (int) ($hit['_id'] ?? 0),
            $hits,
        );

        return [$ids, $total];
    }
}
