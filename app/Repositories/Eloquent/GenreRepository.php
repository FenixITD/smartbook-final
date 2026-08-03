<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreFiltersDto;
use App\Dto\Genre\GenreResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Genre;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Traits\OrdersByIds;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;

/**
 * @extends AbstractEloquentRepository<Genre, GenreResponseDto>
 */
final class GenreRepository extends AbstractEloquentRepository implements GenreRepositoryInterface
{
    use OrdersByIds;

    protected function getModelClass(): string
    {
        return Genre::class;
    }

    protected function getResponseDtoClass(): string
    {
        return GenreResponseDto::class;
    }

    protected function afterCreate(mixed $model): void { Cache::forget('genres.all'); }
    protected function afterUpdate(mixed $model): void { Cache::forget('genres.all'); }
    protected function afterDelete(int $id): void { Cache::forget('genres.all'); }

    /** @return array<int, GenreResponseDto> */
    public function getList(GenreFiltersDto $filters): array
    {
        return $this->query()
            ->when($filters->search !== null, static fn ($q) => $q->where('name', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Genre $genre) => GenreResponseDto::fromModel($genre))
            ->all();
    }

    public function getWebList(GenreFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = $this->query()
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator, static fn (Genre $genre) => GenreResponseDto::fromModel($genre));
    }

    public function getWebListByIds(array $ids, int $total, GenreFiltersDto $filters): PaginatedResponseDto
    {
        if ($ids === []) {
            return PaginatedResponseDto::empty($filters->perPage);
        }

        $items = $this->query()
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->get();

        return $this->createPaginatedResponse($items, $total, $filters->perPage, static fn (Genre $genre) => GenreResponseDto::fromModel($genre));
    }

    /** @return array<int, GenreResponseDto> */
    public function getAll(): array
    {
        return Cache::rememberForever('genres.all', function () {
            return $this->query()->orderBy('name')
                ->select(['id', 'name', 'slug'])
                ->limit(200)
                ->get()
                ->map(static fn (Genre $genre) => GenreResponseDto::fromModel($genre))
                ->all();
        });
    }

    public function findByIdWithRelations(int $id): GenreResponseDto
    {
        /** @var Genre $genre */
        $genre = $this->query()->with(['books'])->withCount('books')->findOrFail($id);

        return GenreResponseDto::fromModel($genre);
    }

    /**
     * @param array<int> $ids
     * @return array<int, GenreResponseDto>
     */
    public function getByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $genres = $this->query()->whereIn('id', $ids)->select(['id', 'name', 'slug'])->get();
        $sorted = $genres->sortBy(static fn ($model): int|string|false => array_search($model->id, $ids, true));

        return $sorted->map(static fn (Genre $genre) => GenreResponseDto::fromModel($genre))->values()->all();
    }

    public function getById(int $id): GenreResponseDto|null
    {
        return $this->executeGetById($id);
    }

    public function create(GenreDto $data): GenreResponseDto
    {
        /** @var GenreResponseDto $response */
        $response = $this->executeCreate($data);

        return $response;
    }

    public function update(int $id, GenreDto $data): GenreResponseDto|null
    {
        /** @var GenreResponseDto|null $response */
        $response = $this->executeUpdate($id, $data);

        return $response;
    }
}
