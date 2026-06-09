<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Dto\Book\BookResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;

class SearchSuggestCatalogBookService
{
    public function __construct(
        private BookRepositoryInterface $repository,
        private SearchBookByQueryService $searchService,
    ) {
    }

    /**
     * @return array<int, array{id: int, title: string, author: string|null, cover_image: string|null, price: float, url: string}>
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
                'cover_image' => $book->coverImage,
                'price' => $book->price,
                'url' => route('catalog.show', $book->slug),
            ],
            $paginated->items,
        ));
    }
}
