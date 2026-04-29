<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Controllers\Controller;
use App\Http\Resources\Book\BookResource;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class GetBookController extends Controller
{
    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
    }

    #[OA\Get(
        path: '/api/books/{book}',
        summary: 'Get book by ID',
        tags: ['Books'],
        parameters: [
            new OA\Parameter(
                parameter: 'book',
                name: 'book',
                description: 'Get a single book by ID',
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
                description: 'Get a single book by ID',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/BookResource'
                )
            ),
        ]
    )]
    public function __invoke(int $bookId): JsonResponse
    {
        $book = $this->repository->getById($bookId);

        return (new BookResource($book))->response();
    }
}
