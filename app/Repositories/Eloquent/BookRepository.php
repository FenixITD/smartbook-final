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
use App\Services\Book\SearchBookForDashboardService;

use function count;

final class BookRepository implements BookRepositoryInterface
{
    public function __construct(private readonly SearchBookForDashboardService $searchService)
    {
    }

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

    /** @param array<int> $ids */
    public function getByIdsWithAuthor(array $ids, int $perPage): PaginatedResponseDto
    {
        $paginator = Book::with('author')
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->paginate($perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    public function getDashboardList(DashboardFiltersDto $filters): PaginatedResponseDto
    {
        $ids = $this->searchService->search($filters);

        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        [$column, $direction] = match ($filters->sort) {
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'newest' => ['publish_year', 'desc'],
            default => ['average_rating', 'desc'],
        };

        $paginator = Book::with(['author', 'genres'])
            ->whereIn('id', $ids)
            ->orderBy($column, $direction)
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    public function getById(int $id): BookResponseDto|null
    {
        $bookId = Book::find($id);

        return $bookId !== null ? BookResponseDto::fromModel($bookId) : null;
    }

    public function findByIdWithRelations(int $id): BookResponseDto
    {
        $bookId = Book::with(['author', 'genres'])->findOrFail($id);

        return BookResponseDto::fromModel($bookId);
    }

    /** @param array<int> $genreIds */
    public function syncBookGenres(int $bookId, array $genreIds): void
    {
        Book::findOrFail($bookId)->genres()->sync($genreIds);
    }

    /** @param array<int> $ids */
    public function getOrderedByIds(array $ids, int $perPage): PaginatedResponseDto
    {
        $paginator = Book::with('author')
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->paginate($perPage)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(
                static fn (Book $book): BookResponseDto => BookResponseDto::fromModel($book)
            )
        );

        return PaginatedResponseDto::fromPaginator($paginator);
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
        return Book::findOrFail($id)->delete();
    }

    /** @param array<int> $ids */
    private function orderByIds(array $ids): string
    {
        $cases = collect($ids)
            ->values()
            ->map(static fn (int $id, int $pos) => "WHEN id = {$id} THEN {$pos}")
            ->implode(' ');

        return 'CASE '.$cases.' ELSE '.count($ids).' END';
    }
}
