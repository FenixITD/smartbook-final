<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Book;

interface BookRepositoryInterface
{
    /** @return array<BookResponseDto> */
    public function getList(BookFiltersDto $filters): array;

    public function getWebList(BookFiltersDto $filters): PaginatedResponseDto;

    public function findModel(int $id): Book;

    public function findModelWithRelations(int $id): Book;

    /** @param array<int> $genreIds */
    public function syncBookGenres(Book $book, array $genreIds): void;

    public function getById(int $id): BookResponseDto|null;

    public function create(BookDto $data): BookResponseDto;

    public function update(int $id, BookDto $data): BookResponseDto|null;

    public function delete(int $id): bool;
}
