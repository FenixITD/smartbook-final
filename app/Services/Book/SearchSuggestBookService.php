<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Dto\Book\BookResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class SearchSuggestBookService
{
    public function __construct(
        private BookRepositoryInterface $repository,
        private SearchBookByQueryService $searchService,
    ) {
    }

    /**
     * @param string $query
     * @return array<int, array{id: int, title: string, author: string|null, url: string}>
     *
     * Fetches up to 5 book suggestions for autocomplete search components, returning basic book details.
     */
    public function execute(string $query): array
    {
        $ids = $this->searchService->search($query, limit: 5);

        if ($ids === []) {
            return [];
        }

        $paginated = $this->repository->getOrderedByIds($ids, perPage: 5);

        return array_values(array_map(
            static fn (BookResponseDto $book): array => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->authorName,
                'url' => route('books.show', $book->id),
            ],
            $paginated->items,
        ));
    }
}
