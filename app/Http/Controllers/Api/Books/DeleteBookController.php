<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteBookController
{
    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $bookId): JsonResponse
    {
        if ($this->repository->getById($bookId) === null) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $this->repository->delete($bookId);

        return response()->json([
            'message' => 'Book deleted successfully',
        ]);
    }
}
