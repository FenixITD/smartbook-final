<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Services\Abstracts\AbstractSearchByQueryService;

class SearchOrderByQueryService extends AbstractSearchByQueryService
{
    protected function getIndexConfigKey(): string
    {
        return 'elasticsearch.orders_index';
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
                            'user_name' => [
                                'query' => $query,
                                'max_expansions' => 10,
                            ],
                        ],
                    ],
                    [
                        'multi_match' => [
                            'query' => $query,
                            'fields' => ['user_name^3'],
                            'fuzziness' => 'AUTO',
                        ],
                    ],
                ],
            ],
        ];
    }
}
