<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Author\AuthorListRequest;
use App\Http\Resources\Author\AuthorResource;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/authors',
    summary: 'Get a list of all authors',
    security: [['bearerAuth' => []]],
    tags: ['Authors'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get a list of all authors',
            content: new OA\JsonContent(
                ref: '#/components/schemas/AuthorResource'
            )
        ),
    ]
)]
final class GetListAuthorController extends Controller
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function __invoke(AuthorListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $authors = $this->repository->getList($filters);

        return AuthorResource::collection($authors)->response();
    }
}
