<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\DTO\AuthorFiltersDTO;
use App\Models\Author;
use Illuminate\Pagination\LengthAwarePaginator;

interface AuthorRepositoryInterface
{
    public function getList(AuthorFiltersDTO $filters): LengthAwarePaginator;

    public function getById(int $id): ?Author;

    public function create(array $data): Author;

    public function update(Author $author, array $data): bool;

    public function delete(Author $author): bool;
}
