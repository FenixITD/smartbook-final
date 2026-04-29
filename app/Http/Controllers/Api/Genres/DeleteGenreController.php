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
        $this->repository->delete($genreId);

        return response()->json([
            'message' => 'Genre deleted successfully',
        ]);
    }
}
