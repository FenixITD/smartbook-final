<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Controllers\Controller;
use App\Http\Resources\Author\AuthorResource;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class GetAuthorController extends Controller
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    #[OA\Get(
        path: '/api/authors/{author}',
        summary: 'Get author by ID',
        tags: ['Authors'],
        parameters: [
            new OA\Parameter(
                parameter: 'author',
                name: 'author',
                description: 'Get a single author by ID',
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
                description: 'Get a single author by ID',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/AuthorResource'
                )
            ),
        ]
    )]
    public function __invoke(int $authorId): JsonResponse
    {
        $author = $this->repository->getById($authorId);

        return (new AuthorResource($author))->response();
    }
}
