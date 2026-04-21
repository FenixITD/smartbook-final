<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Favorite\FavoriteDto;
use App\Dto\Favorite\FavoriteFiltersDto;
use App\Dto\Favorite\FavoriteResponseDto;
use App\Models\Favorite;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;

final class FavoriteRepository implements FavoriteRepositoryInterface
{
    /** @return array<FavoriteResponseDto> */
    public function getList(FavoriteFiltersDto $filters): array
    {
        return Favorite::query()
            ->when($filters->id !== null, static fn ($q) => $q->where('id', $filters->id))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Favorite $favorite) => FavoriteResponseDto::fromModel($favorite))
            ->all();
    }

    public function getById(int $id): FavoriteResponseDto|null
    {
        $favorite = Favorite::find($id);

        return $favorite !== null ? FavoriteResponseDto::fromModel($favorite) : null;
    }

    public function create(FavoriteDto $data): FavoriteResponseDto
    {
        /** @var Favorite $favorite */
        $favorite = Favorite::create($data->toArray());

        return FavoriteResponseDto::fromModel($favorite);
    }

    public function update(int $id, FavoriteDto $data): FavoriteResponseDto|null
    {
        $favorite = Favorite::findOrFail($id);

        $favorite->update($data->toArray());

        /** @var Favorite $fresh */
        $fresh = $favorite->fresh();

        return FavoriteResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) Favorite::destroy($id);
    }
}
