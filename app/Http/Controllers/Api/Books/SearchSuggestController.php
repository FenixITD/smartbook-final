<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Requests\Book\SearchSuggestRequest;
use App\Services\Book\SearchSuggestService;
use Illuminate\Http\JsonResponse;

final readonly class SearchSuggestController
{
    public function __construct(
        private SearchSuggestService $service,
    ) {
    }

    public function __invoke(SearchSuggestRequest $request): JsonResponse
    {
        $suggestions = $this->service->execute($request->searchQuery());

        return response()->json($suggestions);
    }
}
