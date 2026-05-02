<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Genres;

use App\Http\Requests\Genre\GenreListRequest;
use App\Http\Resources\Genre\GenreResource;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/genres',
    summary: 'Get a list of all genres',
    tags: ['Genres'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get a list of all genres',
            content: new OA\JsonContent(
                ref: '#/components/schemas/GenreResource'
            )
        ),
    ]
)]
final readonly class GetListGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function __invoke(GenreListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $genres = $this->repository->getList($filters);

        return GenreResource::collection($genres)->response();
    }
}
