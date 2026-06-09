<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreFiltersDto;
use App\Dto\Genre\GenreResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Genre;
use App\Repositories\Interfaces\GenreRepositoryInterface;

final class GenreRepository implements GenreRepositoryInterface
{
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

    /** @param array<int> $ids */
    public function getWebListByIds(array $ids, GenreFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = Genre::query()
            ->whereIn('id', $ids)
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    /** @return GenreResponseDto[] */
    public function getAll(): array
    {
        return Genre::orderBy('name')
            ->select(['id', 'name', 'slug'])
            ->limit(200)
            ->get()
            ->map(static fn (Genre $genre) => GenreResponseDto::fromModel($genre))
            ->all();
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

        return GenreResponseDto::fromModel($genre);
    }

    public function update(int $id, GenreDto $data): GenreResponseDto|null
    {
        $genre = Genre::findOrFail($id);

        $genre->update($data->toArray());

        /** @var Genre $fresh */
        $fresh = $genre->fresh();

        return GenreResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) Genre::findOrFail($id)->delete();
    }
}
