<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Favorites;

use App\Http\Resources\Favorite\FavoriteResource;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/favorites/{favorite}',
    summary: 'Get favorite by ID',
    security: [['bearerAuth' => []]],
    tags: ['Favorites'],
    parameters: [
        new OA\Parameter(
            name: 'favorite',
            description: 'Get a single favorite by ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
                example: 3,
            )
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get a single favorite by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/FavoriteResource'
            )
        ),
    ]
)]
final readonly class GetFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $favoriteId): JsonResponse
    {
        $favorite = $this->repository->getById($favoriteId);

        return (new FavoriteResource($favorite))->response();
    }
}
