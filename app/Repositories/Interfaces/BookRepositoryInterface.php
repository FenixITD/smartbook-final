<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\DTO\Book\BookFiltersDTO;
use App\DTO\Book\BookResponseDTO;
use App\Models\Book;

interface BookRepositoryInterface
{
    /**
     * @return array<BookResponseDTO>
     */
    public function getList(BookFiltersDTO $filters): array;

    public function getById(int $id): ?BookResponseDTO;

    public function create(array $data): BookResponseDTO;

    public function update(Book $book, array $data): ?BookResponseDTO;

    public function delete(Book $book): bool;
}
