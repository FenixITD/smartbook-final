<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorFiltersDto;
use App\Dto\Author\AuthorResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Author;

interface AuthorRepositoryInterface
{
    /**
     * @return array<AuthorResponseDto>
     */
    public function getList(AuthorFiltersDto $filters): array;

    public function getWebList(AuthorFiltersDto $filters): PaginatedResponseDto;

    public function getById(int $id): ?AuthorResponseDto;

    public function create(AuthorDto $data): AuthorResponseDto;

    public function update(Author $author, AuthorDto $data): ?AuthorResponseDto;

    public function delete(Author $author): bool;
}
