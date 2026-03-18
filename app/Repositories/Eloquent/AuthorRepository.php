<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTO\Author\AuthorFiltersDTO;
use App\DTO\Author\AuthorResponseDTO;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

final class AuthorRepository implements AuthorRepositoryInterface
{
    public function getList(AuthorFiltersDTO $filters): array
    {
        $query = Author::query()
            ->when($filters->search !== null, fn ($q) => $q->where('name', 'like', "%{$filters->search}%"));

        $paginator = $query->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return $paginator->getCollection()
            ->map(fn (Author $author) => AuthorResponseDTO::fromModel($author))->all();
    }

    public function getById(int $id): ?AuthorResponseDTO
    {
        $author = Author::find($id);

        return $author ? AuthorResponseDTO::fromModel($author) : null;
    }

    public function create(array $data): AuthorResponseDTO
    {
        $author = Author::create($data);

        return AuthorResponseDTO::fromModel($author);
    }

    public function update(Author $author, array $data): ?AuthorResponseDTO
    {
        $author->update($data);

        return AuthorResponseDTO::fromModel($author->fresh());
    }

    public function delete(Author $author): bool
    {
        return $author->delete();
    }
}
