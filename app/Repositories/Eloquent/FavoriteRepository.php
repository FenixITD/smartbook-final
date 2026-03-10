<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTO\Favorite\FavoriteFiltersDTO;
use App\DTO\Favorite\FavoriteResponseDTO;
use App\Models\Favorite;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;

final class FavoriteRepository implements FavoriteRepositoryInterface
{
    public function getList(FavoriteFiltersDTO $filters): array
    {
        $query = Favorite::query()
            ->when($filters->search !== null, fn ($q) => $q->where('id', 'like', "%{$filters->search}%"));

        $paginator = $query->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return $paginator->getCollection()
            ->map(fn (Favorite $favorite) => FavoriteResponseDTO::fromModel($favorite))->all();
    }

    public function getById(int $id): ?FavoriteResponseDTO
    {
        $favorite = Favorite::find($id);

        return $favorite ? FavoriteResponseDTO::fromModel($favorite) : null;
    }

    public function create(array $data): FavoriteResponseDTO
    {
        $favorite = Favorite::create($data);

        return FavoriteResponseDTO::fromModel($favorite);
    }

    public function update(Favorite $favorite, array $data): ?FavoriteResponseDTO
    {
        $favorite->update($data);

        return FavoriteResponseDTO::fromModel($favorite->fresh());
    }

    public function delete(Favorite $favorite): bool
    {
        return $favorite->delete();
    }
}
