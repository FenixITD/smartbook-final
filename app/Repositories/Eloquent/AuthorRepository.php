<?php

namespace App\Repositories\Eloquent;

use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthorRepository implements AuthorRepositoryInterface
{
    public function getList(Request $request): LengthAwarePaginator
    {
        $search = $request->string('search')->nullable();
        $perPage = $request->integer('per_page', 15);
        $sortBy = $request->string('sort_by', 'name');
        $sortDirection = $request->string('sort_direction', 'asc');

        $query = Author::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"));

        return $query->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);
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
