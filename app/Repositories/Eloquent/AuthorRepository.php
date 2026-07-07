<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorFiltersDto;
use App\Dto\Author\AuthorResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;

/**
 * @extends AbstractEloquentRepository<Author, AuthorResponseDto>
 */
final class AuthorRepository extends AbstractEloquentRepository implements AuthorRepositoryInterface
{
    protected function getModelClass(): string
    {
        return Author::class;
    }

    protected function getResponseDtoClass(): string
    {
        return AuthorResponseDto::class;
    }

    protected function afterCreate(mixed $model): void { Cache::forget('authors.all'); }
    protected function afterUpdate(mixed $model): void { Cache::forget('authors.all'); }
    protected function afterDelete(int $id): void { Cache::forget('authors.all'); }

    /** @return array<int, AuthorResponseDto> */
    public function getList(AuthorFiltersDto $filters): array
    {
        return $this->query()
            ->when($filters->search !== null, static fn ($q) => $q->where('name', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Author $author) => AuthorResponseDto::fromModel($author))
            ->all();
    }

    public function getWebList(AuthorFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = $this->query()
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator, static fn (Author $author) => AuthorResponseDto::fromModel($author));
    }

    public function getWebListByIds(array $ids, int $total, AuthorFiltersDto $filters): PaginatedResponseDto
    {
        $items = $this->query()
            ->whereIn('id', $ids)
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->get();

        return $this->createPaginatedResponse($items, $total, $filters->perPage, static fn (Author $author) => AuthorResponseDto::fromModel($author));
    }

    /** @return array<int, AuthorResponseDto> */
    public function getAll(): array
    {
        return Cache::rememberForever('authors.all', function () {
            return $this->query()->orderBy('name')
                ->select(['id', 'name'])
                ->limit(200)
                ->get()
                ->map(static fn (Author $author) => AuthorResponseDto::fromModel($author))
                ->all();
        });
    }

    public function getById(int $id): AuthorResponseDto|null
    {
        return $this->executeGetById($id);
    }

    public function findByIdWithRelations(int $id): AuthorResponseDto
    {
        /** @var Author $author */
        $author = $this->query()->with(['books'])->withCount('books')->findOrFail($id);

        return AuthorResponseDto::fromModel($author);
    }

    /**
     * @param array<int> $ids
     * @return array<int, AuthorResponseDto>
     */
    public function getByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $authors = $this->query()->whereIn('id', $ids)->select(['id', 'name'])->get();
        $sorted = $authors->sortBy(static fn ($model): int|string|false => array_search($model->id, $ids, true));

        return $sorted->map(static fn (Author $author) => AuthorResponseDto::fromModel($author))->values()->all();
    }

    public function create(AuthorDto $data): AuthorResponseDto
    {
        /** @var AuthorResponseDto $response */
        $response = $this->executeCreate($data);

        return $response;
    }

    public function update(int $id, AuthorDto $data): AuthorResponseDto|null
    {
        /** @var AuthorResponseDto|null $response */
        $response = $this->executeUpdate($id, $data);

        return $response;
    }
}
