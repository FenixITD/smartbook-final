<?php
declare(strict_types=1);
namespace App\Services\Author;

use App\Dto\Author\AuthorFiltersDto;
use Elastic\Elasticsearch\Client;
use stdClass;

final readonly class SearchAuthorService
{
    public function __construct(private Client $client) {}

    /** @return array<int> */
    public function search(AuthorFiltersDto $filters): array
    {
        $response = $this->client->search([
            'index' => (string) config('elasticsearch.authors_index'),
            'body' => [
                'query' => $this->buildQuery($filters),
                'size' => 10000,
                '_source' => false,
            ],
        ]);

        /** @var array<int, array<string, mixed>> $hits */
        $hits = $response->asArray()['hits']['hits'];

        return array_map(static fn (array $hit): int => (int) $hit['_id'], $hits);
    }

    /** @return array<string, mixed> */
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
