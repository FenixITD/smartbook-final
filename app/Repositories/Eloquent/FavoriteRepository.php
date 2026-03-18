<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Favorite\FavoriteDto;
use App\DTO\Favorite\FavoriteFiltersDto;
use App\DTO\Favorite\FavoriteResponseDto;
use App\Models\Favorite;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;

final class FavoriteRepository implements FavoriteRepositoryInterface
{
    public function getList(FavoriteFiltersDto $filters): array
    {
        return Favorite::query()
            ->when($filters->search !== null, fn ($q) => $q->where('id', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(fn (Favorite $favorite) => FavoriteResponseDto::fromModel($favorite))
            ->all();
    }

    public function getById(int $id): ?FavoriteResponseDto
    {
        $favorite = Favorite::find($id);

        return $favorite ? FavoriteResponseDto::fromModel($favorite) : null;
    }

    public function create(FavoriteDto $data): FavoriteResponseDto
    {
        $favorite = Favorite::create($data->toArray());

        return FavoriteResponseDto::fromModel($favorite);
    }

    public function update(Favorite $favorite, FavoriteDto $data): ?FavoriteResponseDto
    {
        $favorite->update($data->toArray());

        return FavoriteResponseDto::fromModel($favorite->fresh());
    }

    public function delete(Favorite $favorite): bool
    {
        return $favorite->delete();
    }
}
