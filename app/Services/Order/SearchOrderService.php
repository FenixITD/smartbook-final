<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Dto\Order\OrderFiltersDto;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use stdClass;

final readonly class SearchOrderService
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @param OrderFiltersDto $filters
     * @return array
     * @throws ClientResponseException
     * @throws ServerResponseException
     *
     * Searches and filters orders in Elasticsearch based on provided criteria (like ID or status), returning an array of order IDs.
     */
    public function search(OrderFiltersDto $filters): array
    {
        $response = $this->client->search([
            'index' => (string) config('elasticsearch.orders_index'),
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
     * @param OrderFiltersDto $filters
     * @return array<string, mixed>
     *
     * Builds the Elasticsearch query array based on the provided order filters.
     */
    private function buildQuery(OrderFiltersDto $filters): array
    {
        $must = [];
        $filter = [];

        if ($filters->search !== null && $filters->search !== '') {
            $must[] = [
                'multi_match' => [
                    'query' => $filters->search,
                    'fields' => ['status'],
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
