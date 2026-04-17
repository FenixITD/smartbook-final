<?php

declare(strict_types=1);

namespace App\Services\Elasticsearch;

use App\Dto\Book\BookFiltersDto;
use App\Models\Book;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Throwable;

use function count;

final class BookIndexService
{
    private readonly Client $client;

    private readonly string $index;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts([config('elasticsearch.host')])
            ->build();

        $this->index = (string) config('elasticsearch.books_index');
    }

    public function createIndexIfNotExists(): void
    {
        $exists = $this->client->indices()->exists(['index' => $this->index]);

        if ($exists->getStatusCode() === 404) {
            $this->client->indices()->create([
                'index' => $this->index,
                'body' => config('elasticsearch.books_mapping'),
            ]);
        }
    }

    public function indexBook(Book $book): void
    {
        $this->client->index([
            'index' => $this->index,
            'id' => $book->id,
            'body' => $this->toDocument($book),
        ]);
    }

    public function deleteBook(int $bookId): void
    {
        try {
            $this->client->delete([
                'index' => $this->index,
                'id' => $bookId,
            ]);
        } catch (Throwable) {
            // Document may not exist — safe to ignore
        }
    }

    /**
     * @return array{ids: int[], total: int}
     */
    public function search(BookFiltersDto $filters): array
    {
        $query = $filters->search !== null
            ? [
                'multi_match' => [
                    'query' => $filters->search,
                    'fields' => ['title^3', 'description'],
                    'fuzziness' => 'AUTO',
                ],
            ]
            : ['match_all' => (object) []];

        $response = $this->client->search([
            'index' => $this->index,
            'body' => [
                'query' => $query,
                'from' => 0,
                'size' => 1000,
            ],
        ]);

        $hits = $response->asArray()['hits'] ?? [];
        $ids = array_map(
            static fn (array $hit) => (int) $hit['_id'],
            $hits['hits'] ?? [],
        );

        return [
            'ids' => $ids,
            'total' => $hits['total']['value'] ?? 0,
        ];
    }

    /**
     * @param iterable<Book> $books
     */
    public function bulkIndex(iterable $books): void
    {
        $params = ['body' => []];

        foreach ($books as $book) {
            $params['body'][] = [
                'index' => [
                    '_index' => $this->index,
                    '_id' => $book->id,
                ],
            ];
            $params['body'][] = $this->toDocument($book);

            if (count($params['body']) >= 1000) {
                $this->client->bulk($params);
                $params['body'] = [];
            }
        }

        if ($params['body'] !== []) {
            $this->client->bulk($params);
        }
    }

    public function resetIndex(): void
    {
        $exists = $this->client->indices()->exists(['index' => $this->index]);

        if ($exists->getStatusCode() !== 404) {
            $this->client->indices()->delete(['index' => $this->index]);
        }

        $this->client->indices()->create([
            'index' => $this->index,
            'body' => config('elasticsearch.books_mapping'),
        ]);
    }

    /** @return array<string, mixed> */
    private function toDocument(Book $book): array
    {
        return [
            'id' => $book->id,
            'title' => $book->title,
            'slug' => $book->slug,
            'description' => $book->description,
            'author_id' => $book->author_id,
            'price' => $book->price,
            'stock' => $book->stock,
            'publish_year' => $book->publish_year !== null ? (int) $book->publish_year : null,
            'cover_image' => $book->cover_image,
            'average_rating' => $book->average_rating !== null ? (float) $book->average_rating : null,
            'ratings_count' => $book->ratings_count,
            'status' => $book->status,
            'created_at' => $book->created_at?->toIso8601String(),
            'updated_at' => $book->updated_at?->toIso8601String(),
        ];
    }
}
