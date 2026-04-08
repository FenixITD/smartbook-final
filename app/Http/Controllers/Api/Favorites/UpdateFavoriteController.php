<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Favorites;

use App\Http\Requests\Favorite\FavoriteDataRequest;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;

readonly class UpdateFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $repository
    ) {}

    public function __invoke(FavoriteDataRequest $request, int $favorite): JsonResponse
    {
        $updated = $this->repository->update($favorite, $request->toDto());

        return (new FavoriteResource($updated))->response();
    }
}
