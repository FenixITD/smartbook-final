<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Favorites;

use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $favoriteId): JsonResponse
    {
        if ($this->repository->getById($favoriteId) === null) {
            return response()->json(['message' => 'Favorite not found'], 404);
        }

        $this->repository->delete($favoriteId);

        return response()->json([
            'message' => 'Favorite deleted successfully',
        ]);
    }
}
