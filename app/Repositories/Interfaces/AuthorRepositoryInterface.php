<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorFiltersDto;
use App\Dto\Author\AuthorResponseDto;
use App\Dto\PaginatedResponseDto;

interface AuthorRepositoryInterface
{
    /**
     * @return array<AuthorResponseDto>
     */
    public function getList(AuthorFiltersDto $filters): array;

    public function all(): array;

    public function getWebList(AuthorFiltersDto $filters): PaginatedResponseDto;

    public function getById(int $id): ?AuthorResponseDto;

    public function create(AuthorDto $data): AuthorResponseDto;

    public function update(int $id, AuthorDto $data): ?AuthorResponseDto;

    public function delete(int $id): bool;
}
