<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Services\Abstracts\AbstractSearchByQueryService;

class SearchBookByQueryService extends AbstractSearchByQueryService
{
    protected function getIndexConfigKey(): string
    {
        return 'elasticsearch.books_index';
    }

    protected function buildQueryArray(string $query): array
    {
        return [
            'bool' => [
                'should' => [
                    [
                        'match_phrase_prefix' => [
                            'title' => [
                                'query' => $query,
                                'boost' => 3,
                            ],
                        ],
                    ],
                    [
                        'multi_match' => [
                            'query' => $query,
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
}
