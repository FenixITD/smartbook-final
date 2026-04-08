<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Book\BookDto;
use App\DTO\Book\BookFiltersDto;
use App\DTO\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Book;

interface BookRepositoryInterface
{
    /**
     * @return array<BookResponseDto>
     */
    public function getList(BookFiltersDto $filters): array;

    public function getWebList(): PaginatedResponseDto;

    public function findModel(int $id): Book;

    public function findModelWithRelations(int $id): Book;

    public function getById(int $id): ?BookResponseDto;

    public function create(BookDto $data): BookResponseDto;

    public function update(int $id, BookDto $data): ?BookResponseDto;

    public function delete(int $id): bool;
}
