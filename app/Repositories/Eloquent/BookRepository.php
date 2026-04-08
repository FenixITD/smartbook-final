<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Book\BookDto;
use App\DTO\Book\BookFiltersDto;
use App\DTO\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;

final class BookRepository implements BookRepositoryInterface
{
    public function getList(BookFiltersDto $filters): array
    {
        return Book::query()
            ->when($filters->search !== null, fn ($q) => $q->where('title', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(fn (Book $book) => BookResponseDto::fromModel($book))
            ->all();
    }

    public function getWebList(): PaginatedResponseDto
    {
        $paginator = Book::with(['author', 'genres'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

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

    public function getById(int $id): ?BookResponseDto
    {
        $book = Book::find($id);

        return $book ? BookResponseDto::fromModel($book) : null;
    }

    public function create(BookDto $data): BookResponseDto
    {
        $book = Book::create($data->toArray());

        return BookResponseDto::fromModel($book);
    }

    public function update(int $id, BookDto $data): ?BookResponseDto
    {
        $book = Book::findOrFail($id);

        $book->update($data->toArray());

        return BookResponseDto::fromModel($book->fresh());
    }

    public function delete(int $id): bool
    {
        return (bool) Book::destroy($id);
    }
}
