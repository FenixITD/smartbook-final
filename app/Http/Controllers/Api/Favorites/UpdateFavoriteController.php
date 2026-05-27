<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Favorites;

use App\Http\Requests\Favorite\FavoriteDataRequest;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/favorites/{favorite}',
    summary: 'Update favorite by ID',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/FavoriteDataRequest'
        ),
    ),
    tags: ['Favorites'],
    parameters: [
        new OA\Parameter(
            name: 'favorite',
            description: 'Update a single favorite by ID',
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
            description: 'Update a single favorite by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/FavoriteResource'
            )
        ),
    ]
)]
readonly class UpdateFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $repository,
    ) {
    }

    public function __invoke(FavoriteDataRequest $request, int $favoriteId): JsonResponse
    {
        $updatedFavorite = $this->repository->update($favoriteId, $request->toDto());

        return (new FavoriteResource($updatedFavorite))->response();
    }
}
