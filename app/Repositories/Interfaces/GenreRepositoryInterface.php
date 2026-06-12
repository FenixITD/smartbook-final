<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreFiltersDto;
use App\Dto\Genre\GenreResponseDto;
use App\Dto\PaginatedResponseDto;

interface GenreRepositoryInterface
{
    /** @return array<GenreResponseDto> */
    public function getList(GenreFiltersDto $filters): array;

    public function getWebList(GenreFiltersDto $filters): PaginatedResponseDto;

    /** @param array<int> $ids */
    public function getWebListByIds(array $ids, GenreFiltersDto $filters): PaginatedResponseDto;

    /** @return array<mixed> */
    public function getAll(): array;

    public function getById(int $id): GenreResponseDto|null;

    public function findByIdWithRelations(int $id): GenreResponseDto;

    /**
     * @param array<int> $ids
     *
     * @return array<GenreResponseDto>
     */
    public function getByIds(array $ids): array;

    public function create(GenreDto $data): GenreResponseDto;

    public function update(int $id, GenreDto $data): GenreResponseDto|null;

    public function delete(int $id): bool;
}
