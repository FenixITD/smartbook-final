<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authors;

use App\Http\Resources\AuthorResource;
use App\Models\Author;
use App\Services\Author\ShowAuthorService;
use Illuminate\Http\JsonResponse;

readonly class GetByIdAuthorController
{
    public function __construct(
        private ShowAuthorService $service
    ) {}

    public function __invoke(Author $author): JsonResponse
    {
        $authorId = $this->service->execute($author->id);

        if (! $authorId) {
            return response()->json(['success' => false, 'message' => 'Author not found'], 404);
        }

        return (new AuthorResource($authorId))->response();
    }
}
