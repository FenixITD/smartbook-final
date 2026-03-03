<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\DTO\Author\AuthorFiltersDTO;
use App\DTO\Author\AuthorResponseDTO;
use App\Models\Author;

interface AuthorRepositoryInterface
{
    /**
     * @return array<AuthorResponseDTO>
     */
    public function getList(AuthorFiltersDTO $filters): array;

    public function getById(int $id): ?AuthorResponseDTO;

    public function create(array $data): AuthorResponseDTO;

    public function update(Author $author, array $data): ?AuthorResponseDTO;

    public function delete(Author $author): bool;
}
