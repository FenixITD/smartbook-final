<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTO\Book\BookFiltersDTO;
use App\DTO\Book\BookResponseDTO;
use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;

final class BookRepository implements BookRepositoryInterface
{
    public function getList(BookFiltersDTO $filters): array
    {
        $query = Book::query()
            ->when($filters->search !== null, fn ($q) => $q->where('title', 'like', "%{$filters->search}%"));

        $paginator = $query->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return $paginator->getCollection()->map(
            fn (Book $book) => BookResponseDTO::fromModel($book)
        )->all();
    }

    public function getById(int $id): ?BookResponseDTO
    {
        $book = Book::find($id);

        return $book ? BookResponseDTO::fromModel($book) : null;
    }

    public function create(array $data): BookResponseDTO
    {
        $book = Book::create($data);

        return BookResponseDTO::fromModel($book);
    }

    public function update(Book $book, array $data): ?BookResponseDTO
    {
        $book->update($data);

        return BookResponseDTO::fromModel($book->fresh());
    }

    public function delete(Book $book): bool
    {
        return $book->delete();
    }
}
