<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Requests\Book\SearchSuggestRequest;
use App\Services\Author\SearchSuggestAuthorService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/authors/search-suggest',
    summary: 'Author search suggestions',
    security: [['bearerAuth' => []]],
    tags: ['Authors'],
    parameters: [
        new OA\Parameter(
            name: 'q',
            description: 'Search query (min 2 characters)',
            in: 'query',
            required: true,
            schema: new OA\Schema(type: 'string', minLength: 2, example: 'tol')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of author suggestions',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Leo Tolstoy'),
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
        private SearchSuggestAuthorService $service,
    ) {
    }

    public function __invoke(SearchSuggestRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->execute($request->searchQuery())
        );
    }
}
