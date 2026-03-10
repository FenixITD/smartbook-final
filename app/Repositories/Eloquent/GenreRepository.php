<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTO\Genre\GenreFiltersDTO;
use App\DTO\Genre\GenreResponseDTO;
use App\Models\Genre;
use App\Repositories\Interfaces\GenreRepositoryInterface;

final class GenreRepository implements GenreRepositoryInterface
{
    public function getList(GenreFiltersDTO $filters): array
    {
        $query = Genre::query()
            ->when($filters->search !== null, fn ($q) => $q->where('name', 'like', "%{$filters->search}%"));

        $paginator = $query->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return $paginator->getCollection()
            ->map(fn (Genre $genre) => GenreResponseDTO::fromModel($genre))->all();
    }

    public function getById(int $id): ?GenreResponseDTO
    {
        $genre = Genre::find($id);

        return $genre ? GenreResponseDTO::fromModel($genre) : null;
    }

    public function create(array $data): GenreResponseDTO
    {
        $genre = Genre::create($data);

        return GenreResponseDTO::fromModel($genre);
    }

    public function update(Genre $genre, array $data): ?GenreResponseDTO
    {
        $genre->update($data);

        return GenreResponseDTO::fromModel($genre->fresh());
    }

    public function delete(Genre $genre): bool
    {
        return $genre->delete();
    }
}
