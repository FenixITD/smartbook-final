<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Favorites;

use App\Http\Requests\Favorite\FavoriteDataRequest;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;

readonly class CreateFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $repository,
    ) {
    }

    public function __invoke(FavoriteDataRequest $request): JsonResponse
    {
        $favorite = $this->repository->create($request->toDto());

        return (new FavoriteResource($favorite))->response()->setStatusCode(201);
    }
}
