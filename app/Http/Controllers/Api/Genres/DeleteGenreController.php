<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Genres;

use App\Models\Genre;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository
    ) {}

    public function __invoke(int $genre): JsonResponse
    {
        if (! Genre::find($genre)) {
            return response()->json(['message' => 'Genre not found'], 404);
        }

        $this->repository->delete($genre);

        return response()->json([
            'message' => 'Genre deleted successfully',
        ]);
    }
}
