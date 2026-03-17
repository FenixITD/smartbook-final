<?php

declare(strict_types=1);

namespace App\Http\Controllers\Favorites;

use App\Http\Requests\Favorite\FavoriteDataDtoRequest;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Models\Favorite;
use App\Services\Favorite\UpdateFavoriteService;
use Illuminate\Http\JsonResponse;

readonly class UpdateFavoriteController
{
    public function __construct(
        private UpdateFavoriteService $service
    ) {}

    public function __invoke(FavoriteDataDtoRequest $request, Favorite $favorite): JsonResponse
    {
        $updated = $this->service->execute($favorite, $request->toDto());

        return (new FavoriteResource($updated))->response();
    }
}
