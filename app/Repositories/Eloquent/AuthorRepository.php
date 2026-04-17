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
    /** @return array<AuthorResponseDto> */
    public function getList(AuthorFiltersDto $filters): array
    {
        return Author::query()
            ->when($filters->search !== null, static fn ($q) => $q->where('name', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Author $author) => AuthorResponseDto::fromModel($author))
            ->all();
    }

    public function getWebList(AuthorFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = Author::query()
            ->when($filters->search !== null, static fn ($q) => $q->where('name', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    /** @return array<mixed> */
    public function all(): array
    {
        return Author::orderBy('name')->get()->all();
    }

    public function getById(int $id): AuthorResponseDto|null
    {
        $author = Author::find($id);

        return $author !== null ? AuthorResponseDto::fromModel($author) : null;
    }

    public function create(AuthorDto $data): AuthorResponseDto
    {
        /** @var Author $author */
        $author = Author::create($data->toArray());

        return AuthorResponseDto::fromModel($author);
    }

    public function update(int $id, AuthorDto $data): AuthorResponseDto|null
    {
        $author = Author::findOrFail($id);

        $author->update($data->toArray());

        /** @var Author $fresh */
        $fresh = $author->fresh();

        return AuthorResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) Author::destroy($id);
    }
}
