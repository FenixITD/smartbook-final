<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Controllers\Controller;
use App\Services\Book\DeleteBookService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/books/{book}',
    summary: 'Delete book by ID',
    security: [['bearerAuth' => []]],
    tags: ['Books'],
    parameters: [
        new OA\Parameter(
            name: 'book',
            description: 'Delete a single book by ID',
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
            description: 'Delete a single book by ID',
            content: [],
        ),
    ]
)]
final class DeleteBookController extends Controller
{
    public function __construct(
        private DeleteBookService $service,
    ) {
    }

    public function __invoke(int $bookId): JsonResponse
    {
        $this->service->execute($bookId);

        return response()->json([
            'message' => 'Book deleted successfully',
        ]);
    }
}
