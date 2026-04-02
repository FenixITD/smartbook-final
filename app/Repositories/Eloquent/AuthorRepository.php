<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorFiltersDto;
use App\Dto\Author\AuthorResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

final class AuthorRepository implements AuthorRepositoryInterface
{
    public function getList(AuthorFiltersDto $filters): array
    {
        return Author::query()
            ->when($filters->search !== null, fn ($q) => $q->where('name', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(fn (Author $author) => AuthorResponseDto::fromModel($author))
            ->all();
    }

    public function getWebList(AuthorFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = Author::query()
            ->when($filters->search !== null, fn ($q) => $q->where('name', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    public function getById(int $id): ?AuthorResponseDto
    {
        $author = Author::find($id);

        return $author ? AuthorResponseDto::fromModel($author) : null;
    }

    public function create(AuthorDto $data): AuthorResponseDto
    {
        $author = Author::create($data->toArray());

        return AuthorResponseDto::fromModel($author);
    }

    public function update(Author $author, AuthorDto $data): ?AuthorResponseDto
    {
        $author->update($data->toArray());

        return AuthorResponseDto::fromModel($author->fresh());
    }

    public function delete(Author $author): bool
    {
        return $author->delete();
    }
}
