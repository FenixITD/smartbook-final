<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\Dashboard\DashboardFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Book;
use Illuminate\Support\Collection;

interface BookRepositoryInterface
{
    /** @return array<BookResponseDto> */
    public function getList(BookFiltersDto $filters): array;

    /**
     * @param array<int> $ids
     *
     * @return array<BookResponseDto>
     */
    public function getListByIds(array $ids, BookFiltersDto $filters): array;

    public function getWebList(BookFiltersDto $filters): PaginatedResponseDto;

    /**
     * @param array<int> $ids
     */
    public function getWebListByIds(array $ids, BookFiltersDto $filters): PaginatedResponseDto;

    /**
     * @param array<int> $ids
     *
     * @return Collection<int, Book>
     */
    public function getByIdsWithAuthor(array $ids): Collection;

    public function getDashboardList(DashboardFiltersDto $filters): PaginatedResponseDto;

    public function findModel(int $id): Book;

    public function findModelWithRelations(int $id): Book;

    /** @param array<int> $genreIds */
    public function syncBookGenres(Book $book, array $genreIds): void;

    public function getById(int $id): BookResponseDto|null;

    /**
     * @param array<int> $ids
     *
     * @return Collection<int, Book>
     */
    public function getOrderedByIds(array $ids): Collection;

    public function create(BookDto $data): BookResponseDto;

    public function update(int $id, BookDto $data): BookResponseDto|null;

    public function delete(int $id): bool;
}
