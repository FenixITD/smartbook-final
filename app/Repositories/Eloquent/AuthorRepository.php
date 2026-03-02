<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTO\AuthorFiltersDTO;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthorRepository implements AuthorRepositoryInterface
{
    public function getList(AuthorFiltersDTO $filters): LengthAwarePaginator
    {
        $query = Author::query()
            ->when($filters->search, fn ($q) => $q->where('name', 'like', "%{$filters->search}%"));

        return $query->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);
    }

    public function getById(int $id): ?Author
    {
        return Author::find($id);
    }

    public function create(array $data): Author
    {
        return Author::create($data);
    }

    public function update(Author $author, array $data): bool
    {
        return $author->update($data);
    }

    public function delete(Author $author): bool
    {
        return $author->delete();
    }
}
