<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Support\Collection;

final readonly class SearchSuggestService
{
    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Collection<int, array{id: int, title: string, author: string|null, cover_image: string|null,
     *     price: float, url: string}>
     */
    public function execute(string $query): Collection
    {
        /** @var array<int> $ids */
        $ids = Book::search($query)->take(5)->keys()->toArray();

        if ($ids === []) {
            /** @var Collection<int, array{id: int, title: string, author: string|null, cover_image: string|null,
             *     price: float, url: string}> $empty */
            $empty = collect();

            return $empty;
        }

        /** @var Collection<int, Book> $books */
        $books = $this->repository->getOrderedByIds($ids);

        return $books
            ->values()
            ->map(static fn (Book $book): array => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author?->name,
                'cover_image' => $book->cover_image,
                'price' => $book->price,
                'url' => route('dashboard', ['search' => $book->title]),
            ]);
    }
}
