<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Genres;

use App\Http\Requests\Genre\GenreDataRequest;
use App\Http\Resources\Genre\GenreResource;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/genres',
    summary: 'Create genre',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/GenreDataRequest'
        ),
    ),
    tags: ['Genres'],
    responses: [
        new OA\Response(
            response: 201,
            description: 'Get created genre',
            content: new OA\JsonContent(
                ref: '#/components/schemas/GenreResource'
            )
        ),
    ]
)]
readonly class CreateGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function __invoke(GenreDataRequest $request): JsonResponse
    {
        $genre = $this->repository->create($request->toDto());

        return (new GenreResource($genre))->response()->setStatusCode(201);
    }
}
