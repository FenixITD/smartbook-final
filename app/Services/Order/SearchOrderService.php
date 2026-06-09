<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Dto\Order\OrderFiltersDto;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use stdClass;

class SearchOrderService
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @return array<int>
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function search(OrderFiltersDto $filters): array
    {
        $index = config('elasticsearch.orders_index');

        /** @var Elasticsearch $response */
        $response = $this->client->search([
            'index' => is_scalar($index) ? (string) $index : '',
            'body' => [
                'query' => $this->buildQuery($filters),
                'size' => 10000,
                '_source' => false,
            ],
        ]);

        /** @var array{hits: array{hits: array<int, array{_id: int|string}>}} $body */
        $body = $response->asArray();

        $hits = $body['hits']['hits'];

        return array_map(
            static fn (array $hit): int => (int) $hit['_id'],
            $hits,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildQuery(OrderFiltersDto $filters): array
    {
        $must = [];
        $filter = [];

        if ($filters->search !== null && $filters->search !== '') {
            $must[] = [
                'multi_match' => [
                    'query' => $filters->search,
                    'fields' => ['user_name'],
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
