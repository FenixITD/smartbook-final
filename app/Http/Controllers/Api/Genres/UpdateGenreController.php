<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Genres;

use App\Http\Requests\Genre\GenreDataRequest;
use App\Http\Resources\Genre\GenreResource;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/genres/{genre}',
    summary: 'Update genre by ID',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/GenreDataRequest'
        ),
    ),
    tags: ['Genres'],
    parameters: [
        new OA\Parameter(
            name: 'genre',
            description: 'Update a single genre by ID',
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
            description: 'Update a single genre by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/GenreResource'
            )
        ),
    ]
)]
readonly class UpdateGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function __invoke(GenreDataRequest $request, int $genreId): JsonResponse
    {
        $updatedGenre = $this->repository->update($genreId, $request->toDto());

        return (new GenreResource($updatedGenre))->response();
    }
}
