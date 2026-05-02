<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Favorites;

use App\Http\Requests\Favorite\FavoriteListRequest;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/favorites',
    summary: 'Get a list of all favorites',
    tags: ['Favorites'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get a list of all favorites',
            content: new OA\JsonContent(
                ref: '#/components/schemas/FavoriteResource'
            )
        ),
    ]
)]
final readonly class GetListFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $repository,
    ) {
    }

    public function __invoke(FavoriteListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $favorites = $this->repository->getList($filters);

        return FavoriteResource::collection($favorites)->response();
    }
}
