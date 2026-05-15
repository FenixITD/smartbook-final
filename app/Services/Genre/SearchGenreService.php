<?php
declare(strict_types=1);
namespace App\Services\Genre;

use App\Dto\Genre\GenreFiltersDto;
use Elastic\Elasticsearch\Client;
use stdClass;

final readonly class SearchGenreService
{
    public function __construct(private Client $client) {}

    /**
     * @param GenreFiltersDto $filters
     * @return array<int>
     *
     * Searches and filters genres in Elasticsearch based on provided criteria, returning an array of genre IDs.
     */
    public function search(GenreFiltersDto $filters): array
    {
        $response = $this->client->search([
            'index' => (string) config('elasticsearch.genres_index'),
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

    /**
     * @param GenreFiltersDto $filters
     * @return array<string, mixed>
     *
     * Builds the Elasticsearch query array based on the provided genre filters.
     */
    private function buildQuery(GenreFiltersDto $filters): array
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
