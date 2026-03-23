<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTO\Book\BookFiltersDto;
use App\DTO\Book\BookResponseDto;
use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;

final class BookRepository implements BookRepositoryInterface
{
    public function getList(BookFiltersDto $filters): array
    {
        $query = Book::query()
            ->when($filters->search !== null, fn ($q) => $q->where('title', 'like', "%{$filters->search}%"));

        $paginator = $query->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return $paginator->getCollection()
            ->map(fn (Book $book) => BookResponseDto::fromModel($book))->all();
    }

    public function getById(int $id): ?BookResponseDto
    {
        $book = Book::find($id);

        return $book ? BookResponseDto::fromModel($book) : null;
    }

    public function create(array $data): BookResponseDto
    {
        $book = Book::create($data);

        return BookResponseDto::fromModel($book);
    }

    public function update(Book $book, array $data): ?BookResponseDto
    {
        $book->update($data);

        return BookResponseDto::fromModel($book->fresh());
    }

    public function delete(Book $book): bool
    {
        return $book->delete();
    }
}
