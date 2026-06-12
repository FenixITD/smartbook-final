<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reviews;

use App\Http\Requests\Book\SearchSuggestRequest;
use App\Http\Resources\Review\SearchSuggestReviewResource;
use App\Services\Review\SearchSuggestReviewService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/reviews/search-suggest',
    summary: 'Review search suggestions',
    security: [['bearerAuth' => []]],
    tags: ['Reviews'],
    parameters: [
        new OA\Parameter(
            name: 'q',
            description: 'Search query (min 2 characters)',
            in: 'query',
            required: true,
            schema: new OA\Schema(type: 'string', minLength: 2, example: 'goo')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of review suggestions',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/SearchSuggestReviewResource')
            )
        ),
    ]
)]
final readonly class SearchSuggestController
{
    public function __construct(
        private SearchSuggestReviewService $service,
    ) {
    }

    public function __invoke(SearchSuggestRequest $request): JsonResponse
    {
        $suggestions = $this->service->execute($request->searchQuery());

        return response()->json(
            SearchSuggestReviewResource::collection($suggestions)
        );
    }
}
