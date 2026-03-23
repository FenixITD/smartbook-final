<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\DTO\Book\BookFiltersDto;
use App\DTO\Book\BookResponseDto;
use App\Models\Book;

interface BookRepositoryInterface
{
    /**
     * @return array<BookResponseDto>
     */
    public function getList(BookFiltersDto $filters): array;

    public function getById(int $id): ?BookResponseDto;

    public function create(array $data): BookResponseDto;

    public function update(Book $book, array $data): ?BookResponseDto;

    public function delete(Book $book): bool;
}
