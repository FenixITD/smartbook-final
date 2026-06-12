<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Orders;

use App\Http\Requests\Book\SearchSuggestRequest;
use App\Http\Resources\Order\SearchSuggestOrderResource;
use App\Services\Order\SearchSuggestOrderService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/orders/search-suggest',
    summary: 'Order search suggestions',
    security: [['bearerAuth' => []]],
    tags: ['Orders'],
    parameters: [
        new OA\Parameter(
            name: 'q',
            description: 'Search query (min 2 characters)',
            in: 'query',
            required: true,
            schema: new OA\Schema(type: 'string', minLength: 2, example: 'pend')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of order suggestions',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/SearchSuggestOrderResource')
            )
        ),
    ]
)]
final readonly class SearchSuggestController
{
    public function __construct(
        private SearchSuggestOrderService $service,
    ) {
    }

    public function __invoke(SearchSuggestRequest $request): JsonResponse
    {
        $suggestions = $this->service->execute($request->searchQuery());

        return response()->json(
            SearchSuggestOrderResource::collection($suggestions)
        );
    }
}
