<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Requests\Book\BookListRequest;
use App\Http\Resources\Book\BookResource;
use App\Services\Book\SearchBookService;
use Illuminate\Http\JsonResponse;

final readonly class GetListBookController
{
    public function __construct(
        private SearchBookService $searchService,
    ) {
    }

    public function __invoke(BookListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $books = $this->searchService->getList($filters);

        return BookResource::collection($books)->response();
    }
}
