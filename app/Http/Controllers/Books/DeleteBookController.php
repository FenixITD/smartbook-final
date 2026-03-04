<?php

declare(strict_types=1);

namespace App\Http\Controllers\Books;

use App\Models\Book;
use App\Services\Book\DeleteBookService;
use Illuminate\Http\JsonResponse;

readonly class DeleteBookController
{
    public function __construct(
        private DeleteBookService $service
    ) {}

    public function __invoke(Book $book): JsonResponse
    {
        $this->service->execute($book);

        return response()->json([
            'message' => 'Book deleted successfully',
        ]);
    }
}
