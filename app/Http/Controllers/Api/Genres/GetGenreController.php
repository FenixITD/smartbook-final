<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Genres;

use App\Http\Resources\Genre\GenreResource;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/genres/{genre}',
    summary: 'Get genre by ID',
    tags: ['Genres'],
    parameters: [
        new OA\Parameter(
            name: 'genre',
            description: 'Get a single genre by ID',
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
            description: 'Get a single genre by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/GenreResource'
            )
        ),
    ]
)]
final readonly class GetGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $genreId): JsonResponse
    {
        $genre = $this->repository->getById($genreId);

        return (new GenreResource($genre))->response();
    }
}
