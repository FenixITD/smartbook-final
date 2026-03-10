<?php

declare(strict_types=1);

namespace App\Http\Controllers\Favorites;

use App\DTO\Favorite\FavoriteDTO;
use App\Http\Requests\Favorite\FavoriteDataRequest;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Models\Favorite;
use App\Services\Favorite\UpdateFavoriteService;
use Illuminate\Http\JsonResponse;

readonly class UpdateFavoriteController
{
    public function __construct(
        private UpdateFavoriteService $service
    ) {}

    public function __invoke(FavoriteDataRequest $request, Favorite $favorite): JsonResponse
    {
        $dto = FavoriteDTO::fromRequest($request);
        $updated = $this->service->execute($favorite, $dto);

        return (new FavoriteResource($updated))->response();
    }
}
