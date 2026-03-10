<?php

declare(strict_types=1);

namespace App\Http\Controllers\Favorites;

use App\DTO\Favorite\FavoriteDTO;
use App\Http\Requests\Favorite\FavoriteDataRequest;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Services\Favorite\CreateFavoriteService;
use Illuminate\Http\JsonResponse;

readonly class CreateFavoriteController
{
    public function __construct(
        private CreateFavoriteService $service
    ) {}

    public function __invoke(FavoriteDataRequest $request): JsonResponse
    {
        $dto = FavoriteDTO::fromRequest($request);
        $favorite = $this->service->execute($dto);

        return (new FavoriteResource($favorite))->response()->setStatusCode(201);
    }
}
