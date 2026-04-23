<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Author\AuthorDataRequest;
use App\Http\Resources\Author\AuthorResource;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class UpdateAuthorController extends Controller
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    #[OA\Put(
        path: '/api/authors/{author}',
        summary: 'Update author by ID',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/AuthorDataRequest'
            ),
        ),
        tags: ['Authors'],
        parameters: [
            new OA\Parameter(
                parameter: 'author',
                name: 'author',
                description: 'Update a single author by ID',
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
                description: 'Update a single author by ID',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/AuthorResource'
                )
            ),
        ]
    )]
    public function __invoke(AuthorDataRequest $request, int $authorId): JsonResponse
    {
        $updatedAuthor = $this->repository->update($authorId, $request->toDto());

        return (new AuthorResource($updatedAuthor))->response();
    }
}
