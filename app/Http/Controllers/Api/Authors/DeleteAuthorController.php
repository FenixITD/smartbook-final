<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteAuthorController
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $authorId): JsonResponse
    {
        if ($this->repository->getById($authorId) === null) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        $this->repository->delete($authorId);

        return response()->json([
            'message' => 'Author deleted successfully',
        ]);
    }
}
