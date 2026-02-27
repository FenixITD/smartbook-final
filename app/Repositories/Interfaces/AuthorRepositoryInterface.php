<?php

namespace App\Repositories\Interfaces;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface AuthorRepositoryInterface
{
    public function getList(Request $request): LengthAwarePaginator;

    public function getById(int $id): ?Author;

    public function create(array $data): Author;

    public function update(Author $author, array $data): bool;

    public function delete(Author $author): bool;
}
