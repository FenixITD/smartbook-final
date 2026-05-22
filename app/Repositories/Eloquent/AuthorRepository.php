<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorFiltersDto;
use App\Dto\Author\AuthorResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Services\Author\SearchAuthorService;

final class AuthorRepository implements AuthorRepositoryInterface
{
    public function __construct(private readonly SearchAuthorService $searchService)
    {
    }

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
        $ids = $this->searchService->search($filters);

        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        $paginator = Author::query()
            ->whereIn('id', $ids)
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    /** @return AuthorResponseDto[] */
    public function getAll(): array
    {
        return Author::orderBy('name')
            ->select(['id', 'name'])
            ->limit(200)
            ->get()
            ->map(static fn (Author $author) => AuthorResponseDto::fromModel($author))
            ->all();
    }

    public function getById(int $id): AuthorResponseDto|null
    {
        $authorId = Author::find($id);

        return $authorId !== null ? AuthorResponseDto::fromModel($authorId) : null;
    }

    public function findByIdWithRelations(int $id): AuthorResponseDto
    {
        $authorId = Author::with(['books'])
            ->withCount('books')
            ->findOrFail($id);

        return AuthorResponseDto::fromModel($authorId);
    }

    public function suggest(string $query): array
    {
        return Author::orderBy('name')
            ->where('name', 'like', "%{$query}%")
            ->select(['id', 'name'])
            ->limit(20)
            ->get()
            ->map(static fn (Author $author) => AuthorResponseDto::fromModel($author))
            ->all();
    }

    public function create(AuthorDto $data): AuthorResponseDto
    {
        /** @var Author $author */
        $author = Author::create($data->toArray());

        return AuthorResponseDto::fromModel($author);
    }

    public function update(int $id, AuthorDto $data): AuthorResponseDto|null
    {
        $authorId = Author::findOrFail($id);

        $authorId->update($data->toArray());

        /** @var Author $fresh */
        $fresh = $authorId->fresh();

        return AuthorResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) Author::findOrFail($id)->delete();
    }
}
