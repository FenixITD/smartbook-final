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

use App\Traits\CreatesPaginatedResponse;
use App\Traits\OrdersByIds;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

final class BookRepository implements BookRepositoryInterface
{
    use CreatesPaginatedResponse;
    use OrdersByIds;

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
            ->get()
            ->map(static fn (Book $book) => BookResponseDto::fromModel($book))
            ->all();
    }

    public function getWebList(BookFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = Book::with(['author', 'genres'])
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator, static fn (Book $book) => BookResponseDto::fromModel($book));
    }

    public function getWebListByIds(array $ids, int $total, BookFiltersDto $filters): PaginatedResponseDto
    {
        $items = Book::with(['author', 'genres'])
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->get();

        return $this->createPaginatedResponse($items, $total, $filters->perPage, static fn (Book $book) => BookResponseDto::fromModel($book));
    }

    public function getByIdsWithAuthor(array $ids, int $perPage): PaginatedResponseDto
    {
        $paginator = Book::with('author')
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->paginate($perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator, static fn (Book $book) => BookResponseDto::fromModel($book));
    }

    public function getDashboardListByIds(array $ids, int $total, DashboardFiltersDto $filters): PaginatedResponseDto
    {
        [$column, $direction] = match ($filters->sort) {
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'newest' => ['publish_year', 'desc'],
            default => ['average_rating', 'desc'],
        };

        $items = Book::with(['author', 'genres'])
            ->whereIn('id', $ids)
            ->orderBy($column, $direction)
            ->get();

        $currentPage = Paginator::resolveCurrentPage();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $filters->perPage,
            $currentPage !== 0 ? $currentPage : 1,
            ['path' => Paginator::resolveCurrentPath()]
        );
        $paginator->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator, static fn (Book $book) => BookResponseDto::fromModel($book));
    }

    public function getById(int $id): BookResponseDto|null
    {
        $bookId = Book::find($id);

        return $bookId !== null ? BookResponseDto::fromModel($bookId) : null;
    }

    public function getTotalByIdsAndQuantities(array $quantitiesByBookId): string
    {
        $ids = array_keys($quantitiesByBookId);

        return Book::whereIn('id', $ids)
            ->get(['id', 'price'])
            ->reduce(function (string $carry, Book $book) use ($quantitiesByBookId): string {
                return bcadd($carry, bcmul($book->price, (string) $quantitiesByBookId[$book->id], 2), 2);
            }, '0.00');
    }

    public function findByIdWithRelations(int $id): BookResponseDto
    {
        $bookId = Book::with(['author', 'genres'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);

        return BookResponseDto::fromModel($bookId);
    }

    public function findBySlugWithRelations(string $slug): BookResponseDto
    {
        $book = Book::with(['author', 'genres'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('slug', $slug)
            ->firstOrFail();

        return BookResponseDto::fromModel($book);
    }

    /** @param array<int> $ids
     * @return array<BookResponseDto>
     */
    public function findByIdsWithAuthor(array $ids): array
    {
        return Book::with('author')
            ->whereIn('id', $ids)
            ->get()
            ->map(static fn (Book $book) => BookResponseDto::fromModel($book))
            ->all();
    }

    /** @param array<int> $genreIds */
    public function syncBookGenres(int $bookId, array $genreIds): void
    {
        Book::findOrFail($bookId)->genres()->sync($genreIds);
    }

    public function getOrderedByIds(array $ids, int $perPage): PaginatedResponseDto
    {
        $paginator = Book::with('author')
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->paginate($perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator, static fn (Book $book) => BookResponseDto::fromModel($book));
    }

    public function create(BookDto $data): BookResponseDto
    {
        /** @var Book $book */
        $book = Book::create($data->toArray());

        return BookResponseDto::fromModel($book);
    }

    public function update(int $id, BookDto $data): BookResponseDto
    {
        $book = Book::findOrFail($id);

        $book->update($data->toArray());

        /** @var Book $fresh */
        $fresh = $book->fresh();

        return BookResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) Book::findOrFail($id)->delete();
    }

    public function recalculateRating(int $bookId): void
    {
        $book = Book::find($bookId);

        if ($book === null) {
            return;
        }

        $stats = $book->reviews()
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as ratings_count')
            ->first();

        $rawCount = $stats?->getAttribute('ratings_count');
        $ratingsCount = is_numeric($rawCount) ? (int) $rawCount : 0;

        $rawAvg = $stats?->getAttribute('avg_rating');
        $averageRating = is_numeric($rawAvg) ? round((float) $rawAvg, 2) : null;

        $book->update([
            'average_rating' => $ratingsCount > 0 ? $averageRating : null,
            'ratings_count' => $ratingsCount,
        ]);
    }

    public function decrementStock(int $bookId, int $quantity): bool
    {
        $affected = Book::where('id', $bookId)
            ->where('stock', '>=', $quantity)
            ->decrement('stock', $quantity);

        return $affected > 0;
    }

    /** @param array<int> $ids
     * @return array<int, BookResponseDto>
     */
    public function lockForUpdateByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return Book::whereIn('id', $ids)
            ->lockForUpdate()
            ->get()
            ->keyBy('id')
            ->map(static fn (Book $book) => BookResponseDto::fromModel($book))
            ->all();
    }
}
