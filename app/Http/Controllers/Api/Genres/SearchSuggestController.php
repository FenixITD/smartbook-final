<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Genres;

use App\Http\Requests\Book\SearchSuggestRequest;
use App\Services\Genre\SearchSuggestGenreService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/genres/search-suggest',
    summary: 'Genre search suggestions',
    security: [['sanctum' => []]],
    tags: ['Genres'],
    parameters: [
        new OA\Parameter(
            name: 'q',
            description: 'Search query (min 2 characters)',
            in: 'query',
            required: true,
            schema: new OA\Schema(type: 'string', minLength: 2, example: 'fan')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of genre suggestions',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Fantasy'),
                        new OA\Property(property: 'slug', type: 'string', example: 'fantasy'),
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
        private SearchSuggestGenreService $service,
    ) {
    }

    public function __invoke(SearchSuggestRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->execute($request->searchQuery())
        );
    }
}
