<?php

declare(strict_types=1);

namespace App\Http\Controllers\Favorites;

use App\Models\Favorite;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $repository
    ) {}

    public function __invoke(Favorite $favorite): JsonResponse
    {
        $this->repository->delete($favorite);

        return response()->json([
            'message' => 'Favorite deleted successfully',
        ]);
    }
}
