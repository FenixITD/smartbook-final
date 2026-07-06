<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreFiltersDto;
use App\Dto\Genre\GenreResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Genre;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Traits\CreatesPaginatedResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

final class GenreRepository implements GenreRepositoryInterface
{
    use CreatesPaginatedResponse;

    /** @return array<GenreResponseDto> */
    public function getList(GenreFiltersDto $filters): array
    {
        return Genre::query()
            ->when($filters->search !== null, static fn ($q) => $q->where('name', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Genre $genre) => GenreResponseDto::fromModel($genre))
            ->all();
    }

    public function getWebList(GenreFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = Genre::query()
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    public function getWebListByIds(array $ids, int $total, GenreFiltersDto $filters): PaginatedResponseDto
    {
        $items = Genre::query()
            ->whereIn('id', $ids)
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->get();

        return $this->createPaginatedResponse($items, $total, $filters->perPage);
    }

    /** @return GenreResponseDto[] */
    public function getAll(): array
    {
        return Cache::rememberForever('genres.all', function () {
            return Genre::orderBy('name')
                ->select(['id', 'name', 'slug'])
                ->limit(200)
                ->get()
                ->map(static fn (Genre $genre) => GenreResponseDto::fromModel($genre))
                ->all();
        });
    }

    public function getById(int $id): GenreResponseDto|null
    {
        $genreId = Genre::find($id);

        return $genreId !== null ? GenreResponseDto::fromModel($genreId) : null;
    }

    public function findByIdWithRelations(int $id): GenreResponseDto
    {
        $genreId = Genre::with(['books'])
            ->withCount('books')
            ->findOrFail($id);

        return GenreResponseDto::fromModel($genreId);
    }

    /**
     * @param array<int> $ids
     *
     * @return array<GenreResponseDto>
     */
    public function getByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $genres = Genre::whereIn('id', $ids)
            ->select(['id', 'name', 'slug'])
            ->get();

        $sorted = $genres->sortBy(static fn (Genre $genre) => array_search($genre->id, $ids, true));

        return $sorted->map(static fn (Genre $genre) => GenreResponseDto::fromModel($genre))
            ->values()
            ->all();
    }

    public function create(GenreDto $data): GenreResponseDto
    {
        /** @var Genre $genre */
        $genre = Genre::create($data->toArray());

        Cache::forget('genres.all');

        return GenreResponseDto::fromModel($genre);
    }

    public function update(int $id, GenreDto $data): GenreResponseDto
    {
        $genre = Genre::findOrFail($id);

        $genre->update($data->toArray());

        /** @var Genre $fresh */
        $fresh = $genre->fresh();

        Cache::forget('genres.all');

        return GenreResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        $deleted = (bool) Genre::findOrFail($id)->delete();

        if ($deleted) {
            Cache::forget('genres.all');
        }

        return $deleted;
    }
}
