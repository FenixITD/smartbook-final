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

    public function getWebList(GenreFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = Genre::query()
            ->when($filters->search !== null, static fn ($q) => $q->where('name', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    /** @return array<mixed> */
    public function getAll(): array
    {
        return Genre::orderBy('name')->lazy(15)->toArray();
    }

    public function getById(int $id): GenreResponseDto|null
    {
        $genreId = Genre::find($id);

        return $genreId !== null ? GenreResponseDto::fromModel($genreId) : null;
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
        return Genre::findOrFail($id)->delete();
    }
}
