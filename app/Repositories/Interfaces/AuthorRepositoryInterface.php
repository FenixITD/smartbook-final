<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Author\AuthorDto;
use App\DTO\Author\AuthorFiltersDto;
use App\DTO\Author\AuthorResponseDto;
use App\Models\Author;

interface AuthorRepositoryInterface
{
    /**
     * @return array<AuthorResponseDto>
     */
    public function getList(AuthorFiltersDto $filters): array;

    public function getById(int $id): ?AuthorResponseDto;

    public function create(AuthorDto $data): AuthorResponseDto;

    public function update(Author $author, AuthorDto $data): ?AuthorResponseDto;

    public function delete(Author $author): bool;
}
