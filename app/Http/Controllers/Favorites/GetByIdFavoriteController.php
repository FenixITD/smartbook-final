<?php

declare(strict_types=1);

namespace App\Http\Controllers\Favorites;

use App\Http\Resources\Favorite\FavoriteResource;
use App\Models\Favorite;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetByIdFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $repository
    ) {}

    public function __invoke(Favorite $favorite): JsonResponse
    {
        $favoriteId = $this->repository->getById($favorite->id);

        return (new FavoriteResource($favoriteId))->response();
    }
}
