<?php

declare(strict_types=1);

namespace App\Http\Controllers\Books;

use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteBookController
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function __invoke(Book $book): JsonResponse
    {
        $this->repository->delete($book);

        return response()->json([
            'message' => 'Book deleted successfully',
        ]);
    }
}
