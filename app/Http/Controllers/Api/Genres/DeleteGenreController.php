<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Genres;

use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $genreId): JsonResponse
    {
        if ($this->repository->getById($genreId) === null) {
            return response()->json(['message' => 'Genre not found'], 404);
        }

        $this->repository->delete($genreId);

        return response()->json([
            'message' => 'Genre deleted successfully',
        ]);
    }
}
