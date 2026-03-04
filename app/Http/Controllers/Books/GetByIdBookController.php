<?php

declare(strict_types=1);

namespace App\Http\Controllers\Books;

use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use App\Services\Book\ShowBookService;
use Illuminate\Http\JsonResponse;

readonly class GetByIdBookController
{
    public function __construct(
        private ShowBookService $service
    ) {}

    public function __invoke(Book $book): JsonResponse
    {
        $bookId = $this->service->execute($book->id);

        if (! $bookId) {
            return response()->json(['success' => false, 'message' => 'Book not found'], 404);
        }

        return (new BookResource($bookId))->response();
    }
}
