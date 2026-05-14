<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Requests\Book\SearchSuggestRequest;
use App\Services\Book\SearchSuggestCatalogBookService;
use Illuminate\Http\JsonResponse;

final readonly class SearchSuggestCatalogBookController
{
    public function __construct(
        private SearchSuggestCatalogBookService $service,
    ) {}

    public function __invoke(SearchSuggestRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->execute($request->searchQuery())
        );
    }
}
