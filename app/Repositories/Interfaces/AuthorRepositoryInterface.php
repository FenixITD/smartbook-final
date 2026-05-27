<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorFiltersDto;
use App\Dto\Author\AuthorResponseDto;
use App\Dto\PaginatedResponseDto;

interface AuthorRepositoryInterface
{
    /** @return array<AuthorResponseDto> */
    public function getList(AuthorFiltersDto $filters): array;

    public function getWebListByIds(array $ids, AuthorFiltersDto $filters): PaginatedResponseDto;

    /** @return array<mixed> */
    public function getAll(): array;

    public function getById(int $id): AuthorResponseDto|null;

    public function findByIdWithRelations(int $id): AuthorResponseDto;

    /** @return AuthorResponseDto[] */
    public function suggest(string $query): array;

    public function create(AuthorDto $data): AuthorResponseDto;

    public function update(int $id, AuthorDto $data): AuthorResponseDto|null;

    public function delete(int $id): bool;
}
