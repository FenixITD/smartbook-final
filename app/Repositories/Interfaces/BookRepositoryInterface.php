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
     * @param array<int> $ids
     *
     * @return array<BookResponseDto>
     */
    public function getListByIds(array $ids, BookFiltersDto $filters): array;

    public function getWebList(BookFiltersDto $filters): PaginatedResponseDto;

    /** @param array<int> $ids */
    public function getWebListByIds(array $ids, BookFiltersDto $filters): PaginatedResponseDto;

    /** @param array<int> $ids */
    public function getByIdsWithAuthor(array $ids, int $perPage): PaginatedResponseDto;

    public function getDashboardListByIds(array $ids, DashboardFiltersDto $filters): PaginatedResponseDto;

    public function getById(int $id): BookResponseDto|null;

    /** @param array<int, int> $quantitiesByBookId */
    public function getTotalByIdsAndQuantities(array $quantitiesByBookId): float;

    public function findByIdWithRelations(int $id): BookResponseDto;

    /** @param array<int> $ids
     * @return array<BookResponseDto>
     */
    public function findByIdsWithAuthor(array $ids): array;

    /** @param array<int> $genreIds */
    public function syncBookGenres(int $bookId, array $genreIds): void;

    /** @param array<int> $ids */
    public function getOrderedByIds(array $ids, int $perPage): PaginatedResponseDto;

    public function create(BookDto $data): BookResponseDto;

    public function update(int $id, BookDto $data): BookResponseDto|null;

    public function delete(int $id): bool;
}
