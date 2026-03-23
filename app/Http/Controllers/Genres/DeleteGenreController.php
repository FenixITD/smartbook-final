<?php

declare(strict_types=1);

namespace App\Http\Controllers\Genres;

use App\Models\Genre;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository
    ) {}

    public function __invoke(Genre $genre): JsonResponse
    {
        $this->repository->delete($genre);

        return response()->json([
            'message' => 'Genre deleted successfully',
        ]);
    }
}
