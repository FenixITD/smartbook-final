<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\Dashboard\DashboardFiltersDto;
use App\Dto\PaginatedResponseDto;

interface BookRepositoryInterface
{
    /** @return array<BookResponseDto> */
    public function getList(BookFiltersDto $filters): array;

    /**
     * @param  array<int>  $ids
     * @return array<BookResponseDto>
     */
    public function getListByIds(array $ids, BookFiltersDto $filters): array;

    public function getWebList(BookFiltersDto $filters): PaginatedResponseDto;

    /** @param array<int> $ids */
    public function getWebListByIds(array $ids, int $total, BookFiltersDto $filters): PaginatedResponseDto;

    /** @param array<int> $ids */
    public function getByIdsWithAuthor(array $ids, int $perPage, bool $showNonActive = false): PaginatedResponseDto;

    /** @param array<int> $ids */
    public function getDashboardListByIds(array $ids, int $total, DashboardFiltersDto $filters): PaginatedResponseDto;

    public function getById(int $id): ?BookResponseDto;

    /** @param array<int, int> $quantitiesByBookId */
    public function getTotalByIdsAndQuantities(array $quantitiesByBookId): string;

    public function findByIdWithRelations(int $id): BookResponseDto;

    public function findBySlugWithRelations(string $slug): BookResponseDto;

    /** @param array<int> $ids
     * @return array<BookResponseDto>
     */
    public function findByIdsWithAuthor(array $ids): array;

    /** @param array<int> $genreIds */
    public function syncBookGenres(int $bookId, array $genreIds): void;

    /** @param array<int> $ids */
    public function getOrderedByIds(array $ids, int $perPage): PaginatedResponseDto;

    /** @param array<int> $ids */
    public function getOrderedActiveByIds(array $ids, int $perPage): PaginatedResponseDto;

    public function create(BookDto $data): BookResponseDto;

    public function update(int $id, BookDto $data): ?BookResponseDto;

    public function delete(int $id): bool;

    public function recalculateRating(int $bookId): void;

    /**
     * @return bool true if the stock was decremented, false if there was not enough stock left.
     */
    public function decrementStock(int $bookId, int $quantity): bool;

    /** @return bool true if the stock was incremented, false if the book does not exist. */
    public function incrementStock(int $bookId, int $quantity): bool;

    /**
     * @param  array<int>  $ids
     * @return array<int, BookResponseDto>
     */
    public function lockForUpdateByIds(array $ids): array;
}
