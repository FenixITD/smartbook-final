<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authors;

use App\Models\Author;
use App\Services\Author\GetByIdAuthorService;
use Illuminate\Http\JsonResponse;

readonly class GetByIdAuthorController
{
    public function __construct(
        private GetByIdAuthorService $service
    ) {}

    public function __invoke(Author $author): JsonResponse
    {
        $authorId = $this->service->execute($author->id);

        if (! $authorId) {
            return response()->json(['success' => false, 'message' => 'Author not found'], 404);
        }

        return response()->json([
            'author' => $authorId,
        ]);
    }
}
