<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Favorite\FavoriteDto;
use App\Dto\Favorite\FavoriteFiltersDto;
use App\Dto\Favorite\FavoriteResponseDto;
use App\Models\Favorite;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use UnexpectedValueException;

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
        $favoriteId = Favorite::find($id);

        return $favoriteId !== null ? FavoriteResponseDto::fromModel($favoriteId) : null;
    }

    public function create(FavoriteDto $data): FavoriteResponseDto
    {
        /** @var Favorite $favorite */
        $favorite = Favorite::create($data->toArray());

        return FavoriteResponseDto::fromModel($favorite);
    }

    public function update(int $id, FavoriteDto $data): FavoriteResponseDto
    {
        $favorite = Favorite::findOrFail($id);

        $favorite->update($data->toArray());

        /** @var Favorite $fresh */
        $fresh = $favorite->fresh();

        return FavoriteResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) Favorite::findOrFail($id)->delete();
    }

    public function toggle(int $userId, int $bookId): bool
    {
        $deleted = Favorite::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->delete();

        if ($deleted > 0) {
            return false;
        }

        Favorite::insertOrIgnore([
            'user_id' => $userId,
            'book_id' => $bookId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    /** @return array<int> */
    public function getBookIdsByUser(int $userId): array
    {
        return Favorite::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->pluck('book_id')
            ->map(static fn (mixed $id) => is_numeric($id) ? (int) $id : throw new UnexpectedValueException('book_id is not numeric'))
            ->all();
    }
}
