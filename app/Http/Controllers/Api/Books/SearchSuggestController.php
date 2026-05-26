<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Requests\Book\SearchSuggestRequest;
use App\Services\Book\SearchSuggestBookService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/books/search-suggest',
    summary: 'Book search suggestions (admin)',
    security: [['sanctum' => []]],
    tags: ['Books'],
    parameters: [
        new OA\Parameter(
            name: 'q',
            description: 'Search query (min 2 characters)',
            in: 'query',
            required: true,
            schema: new OA\Schema(type: 'string', minLength: 2, example: 'harry')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of book suggestions',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Harry Potter'),
                    ],
                    type: 'object',
                )
            )
        ),
    ]
)]
final readonly class SearchSuggestController
{
    public function __construct(
        private SearchSuggestBookService $service,
    ) {
    }

    public function __invoke(SearchSuggestRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->execute($request->searchQuery())
        );
    }
}
