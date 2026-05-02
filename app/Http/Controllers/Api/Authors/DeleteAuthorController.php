<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/authors/{author}',
    summary: 'Delete author by ID',
    tags: ['Authors'],
    parameters: [
        new OA\Parameter(
            name: 'author',
            description: 'Delete a single author by ID',
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
            description: 'Delete a single author by ID',
            content: [],
        ),
    ]
)]
final class DeleteAuthorController extends Controller
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $authorId): JsonResponse
    {
        $this->repository->delete($authorId);

        return response()->json([
            'message' => 'Author deleted successfully',
        ]);
    }
}
