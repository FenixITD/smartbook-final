<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Author\AuthorDataRequest;
use App\Http\Resources\Author\AuthorResource;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class CreateAuthorController extends Controller
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    #[OA\Post(
        path: '/api/authors',
        summary: 'Create author',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/AuthorDataRequest'
            ),
        ),
        tags: ['Authors'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Get created author',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/AuthorResource'
                )
            ),
        ]
    )]
    public function __invoke(AuthorDataRequest $request): JsonResponse
    {
        $author = $this->repository->create($request->toDto());

        return (new AuthorResource($author))->response()->setStatusCode(201);
    }
}
