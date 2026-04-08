<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Favorites;

use App\Http\Requests\Favorite\FavoriteListRequest;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $repository
    ) {}

    public function __invoke(FavoriteListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $favorites = $this->repository->getList($filters);

        return FavoriteResource::collection($favorites)->response();
    }
}
