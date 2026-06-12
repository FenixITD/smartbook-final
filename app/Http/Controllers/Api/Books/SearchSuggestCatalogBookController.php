<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Requests\Book\SearchSuggestRequest;
use App\Http\Resources\Book\SearchSuggestCatalogBookResource;
use App\Services\Book\SearchSuggestCatalogBookService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/books/catalog-search-suggest',
    summary: 'Book search suggestions for public catalog',
    security: [['bearerAuth' => []]],
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
            description: 'List of book suggestions for catalog',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/SearchSuggestCatalogBookResource')
            )
        ),
    ]
)]
final readonly class SearchSuggestCatalogBookController
{
    public function __construct(
        private SearchSuggestCatalogBookService $service,
    ) {
    }

    public function __invoke(SearchSuggestRequest $request): JsonResponse
    {
        $suggestions = $this->service->execute($request->searchQuery());

        return response()->json(
            SearchSuggestCatalogBookResource::collection($suggestions)
        );
    }
}
