<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Requests\Author\SearchSuggestRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class SearchSuggestController
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {}

    public function __invoke(SearchSuggestRequest $request): JsonResponse
    {
        $authors = $this->repository->suggest($request->searchQuery());

        return response()->json($authors);
    }
}
