<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Elasticsearch\BookIndexService;

use function count;

final class BookRepository implements BookRepositoryInterface
{
    public function __construct(
        private readonly BookIndexService $searchService,
    ) {
    }

    /** @return array<BookResponseDto> */
    public function getList(BookFiltersDto $filters): array
    {
        if ($filters->search !== null) {
            return $this->searchWithElasticsearch($filters);
        }

        return Book::query()
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Book $book) => BookResponseDto::fromModel($book))
            ->all();
    }

    public function getWebList(BookFiltersDto $filters): PaginatedResponseDto
    {
        if ($filters->search !== null) {
            return $this->searchWebWithElasticsearch($filters);
        }

        $paginator = Book::with(['author', 'genres'])
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    public function findModel(int $id): Book
    {
        return Book::findOrFail($id);
    }

    public function findModelWithRelations(int $id): Book
    {
        return Book::with(['author', 'genres'])->findOrFail($id);
    }

    /** @param array<int> $genreIds */
    public function syncBookGenres(Book $book, array $genreIds): void
    {
        $book->genres()->sync($genreIds);
    }

    public function getById(int $id): BookResponseDto|null
    {
        $book = Book::find($id);

        return $book !== null ? BookResponseDto::fromModel($book) : null;
    }

    public function create(BookDto $data): BookResponseDto
    {
        /** @var Book $book */
        $book = Book::create($data->toArray());

        return BookResponseDto::fromModel($book);
    }

    public function update(int $id, BookDto $data): BookResponseDto|null
    {
        $book = Book::findOrFail($id);

        $book->update($data->toArray());

        /** @var Book $fresh */
        $fresh = $book->fresh();

        return BookResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) Book::destroy($id);
    }

    // -------------------------------------------------------------------------

    /** @return array<BookResponseDto> */
    private function searchWithElasticsearch(BookFiltersDto $filters): array
    {
        $result = $this->searchService->search($filters);

        if ($result['ids'] === []) {
            return [];
        }

        $ids = $result['ids'];

        return Book::query()
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Book $book) => BookResponseDto::fromModel($book))
            ->all();
    }

    private function searchWebWithElasticsearch(BookFiltersDto $filters): PaginatedResponseDto
    {
        $result = $this->searchService->search($filters);

        if ($result['ids'] === []) {
            $paginator = Book::query()
                ->whereRaw('1 = 0')
                ->with(['author', 'genres'])
                ->paginate($filters->perPage)
                ->withQueryString();

            return PaginatedResponseDto::fromPaginator($paginator);
        }

        $ids = $result['ids'];

        $paginator = Book::with(['author', 'genres'])
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    /** @param array<int> $ids */
    private function orderByIds(array $ids): string
    {
        $cases = collect($ids)
            ->values()
            ->map(static fn (int $id, int $pos) => "WHEN id = {$id} THEN {$pos}")
            ->implode(' ');

        return "CASE {$cases} ELSE ".count($ids).' END';
    }
}
