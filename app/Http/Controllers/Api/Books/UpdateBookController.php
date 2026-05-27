<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Controllers\Controller;
use App\Http\Requests\Book\BookDataRequest;
use App\Http\Resources\Book\BookResource;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/books/{book}',
    summary: 'Update book by ID',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/BookDataRequest'
        ),
    ),
    tags: ['Books'],
    parameters: [
        new OA\Parameter(
            name: 'book',
            description: 'Update a single book by ID',
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
            description: 'Update a single book by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/BookResource'
            )
        ),
    ]
)]
final class UpdateBookController extends Controller
{
    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
    }

    public function __invoke(BookDataRequest $request, int $bookId): JsonResponse
    {
        $updatedBook = $this->repository->update($bookId, $request->toDto());

        return (new BookResource($updatedBook))->response();
    }
}
