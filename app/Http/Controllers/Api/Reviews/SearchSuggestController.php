<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reviews;

use App\Http\Requests\Book\SearchSuggestRequest;
use App\Services\Review\SearchSuggestReviewService;
use Illuminate\Http\JsonResponse;

final readonly class SearchSuggestController
{
    public function __construct(
        private SearchSuggestReviewService $service,
    ) {
    }

    public function __invoke(SearchSuggestRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->execute($request->searchQuery())
        );
    }
}
