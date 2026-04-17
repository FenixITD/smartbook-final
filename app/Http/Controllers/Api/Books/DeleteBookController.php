<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteBookController
{
    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $book): JsonResponse
    {
        if (Book::find($book) === null) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $this->repository->delete($book);

        return response()->json([
            'message' => 'Book deleted successfully',
        ]);
    }
}
