<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\Dashboard\DashboardFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Support\Collection;

use function count;

final class BookRepository implements BookRepositoryInterface
{
    /** @return array<BookResponseDto> */
    public function getList(BookFiltersDto $filters): array
    {
        return Book::query()
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Book $book) => BookResponseDto::fromModel($book))
            ->all();
    }

    /**
     * @param array<int> $ids
     *
     * @return array<BookResponseDto>
     */
    public function getListByIds(array $ids, BookFiltersDto $filters): array
    {
        return Book::query()
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Book $book) => BookResponseDto::fromModel($book))
            ->all();
    }

    public function getWebList(BookFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = Book::with(['author', 'genres'])
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    /** @param array<int> $ids */
    public function getWebListByIds(array $ids, BookFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = Book::with(['author', 'genres'])
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    /**
     * @param array<int> $ids
     *
     * @return Collection<int, Book>
     */
    public function getByIdsWithAuthor(array $ids): Collection
    {
        return Book::with('author')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    public function getDashboardList(DashboardFiltersDto $filters): PaginatedResponseDto
    {
        [$column, $direction] = match ($filters->sort) {
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'newest' => ['publish_year', 'desc'],
            default => ['average_rating', 'desc'],
        };

        $paginator = Book::with(['author', 'genres'])
            ->when($filters->search, static fn ($q) => $q->where('title', 'like', "%{$filters->search}%"))
            ->when($filters->genre, static fn ($q) => $q->whereHas('genres', static fn ($q) => $q->where('genres.id', $filters->genre)))
            ->when($filters->author, static fn ($q) => $q->where('author_id', $filters->author))
            ->when($filters->year, static fn ($q) => $q->where('publish_year', $filters->year))
            ->when($filters->status, static fn ($q) => $q->where('status', $filters->status))
            ->orderBy($column, $direction)
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

    /**
     * @param array<int> $ids
     *
     * @return Collection<int, Book>
     */
    public function getOrderedByIds(array $ids): Collection
    {
        $cases = collect($ids)
            ->values()
            ->map(static fn (mixed $id, int $pos) => "WHEN id = {$id} THEN {$pos}")
            ->implode(' ');

        $orderBy = 'CASE '.$cases.' ELSE '.count($ids).' END';

        return Book::with('author')
            ->whereIn('id', $ids)
            ->orderByRaw($orderBy)
            ->get();
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
