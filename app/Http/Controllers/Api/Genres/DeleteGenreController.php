<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Genres;

use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/genres/{genre}',
    summary: 'Delete genre by ID',
    security: [['bearerAuth' => []]],
    tags: ['Genres'],
    parameters: [
        new OA\Parameter(
            name: 'genre',
            description: 'Delete a single genre by ID',
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
            description: 'Delete a single genre by ID',
            content: [],
        ),
    ]
)]
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
