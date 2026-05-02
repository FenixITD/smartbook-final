<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Favorites;

use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/favorites/{favorite}',
    summary: 'Delete favorite by ID',
    tags: ['Favorites'],
    parameters: [
        new OA\Parameter(
            name: 'favorite',
            description: 'Delete a single favorite by ID',
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
            description: 'Delete a single favorite by ID',
            content: [],
        ),
    ]
)]
final readonly class DeleteFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $favoriteId): JsonResponse
    {
        $this->repository->delete($favoriteId);

        return response()->json([
            'message' => 'Favorite deleted successfully',
        ]);
    }
}
