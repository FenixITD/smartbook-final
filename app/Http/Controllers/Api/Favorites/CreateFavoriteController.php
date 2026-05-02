<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Favorites;

use App\Http\Requests\Favorite\FavoriteDataRequest;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/favorites',
    summary: 'Create favorite',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/FavoriteDataRequest'
        ),
    ),
    tags: ['Favorites'],
    responses: [
        new OA\Response(
            response: 201,
            description: 'Get created favorite',
            content: new OA\JsonContent(
                ref: '#/components/schemas/FavoriteResource'
            )
        ),
    ]
)]
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
