<?php

declare(strict_types=1);

namespace App\Http\Controllers\Favorites;

use App\DTO\Favorite\FavoriteFiltersDTO;
use App\Http\Requests\Favorite\FavoriteListRequest;
use App\Http\Resources\Favorite\FavoriteCollection;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $repository
    ) {}

    public function __invoke(FavoriteListRequest $request): JsonResponse
    {
        $filters = FavoriteFiltersDTO::fromRequest($request);
        $favorites = $this->repository->getList($filters);

        return (new FavoriteCollection($favorites))->response();
    }
}
