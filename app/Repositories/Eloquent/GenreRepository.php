<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreFiltersDto;
use App\Dto\Genre\GenreResponseDto;
use App\Models\Genre;
use App\Repositories\Interfaces\GenreRepositoryInterface;

final class GenreRepository implements GenreRepositoryInterface
{
    public function getList(GenreFiltersDto $filters): array
    {
        return Genre::query()
            ->when($filters->search !== null, fn ($q) => $q->where('name', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(fn (Genre $genre) => GenreResponseDto::fromModel($genre))
            ->all();
    }

    public function getById(int $id): ?GenreResponseDto
    {
        $genre = Genre::find($id);

        return $genre ? GenreResponseDto::fromModel($genre) : null;
    }

    public function create(GenreDto $data): GenreResponseDto
    {
        $genre = Genre::create($data->toArray());

        return GenreResponseDto::fromModel($genre);
    }

    public function update(Genre $genre, GenreDto $data): ?GenreResponseDto
    {
        $genre->update($data->toArray());

        return GenreResponseDto::fromModel($genre->fresh());
    }

    public function delete(Genre $genre): bool
    {
        return $genre->delete();
    }
}
