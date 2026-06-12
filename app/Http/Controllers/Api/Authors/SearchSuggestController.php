<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Requests\Book\SearchSuggestRequest;
use App\Http\Resources\Author\SearchSuggestAuthorResource;
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
                items: new OA\Items(ref: '#/components/schemas/SearchSuggestAuthorResource')
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
        $suggestions = $this->service->execute($request->searchQuery());

        return response()->json(
            SearchSuggestAuthorResource::collection($suggestions)
        );
    }
}
