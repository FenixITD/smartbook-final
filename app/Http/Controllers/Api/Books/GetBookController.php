<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetBookController
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function __invoke(Book $book): JsonResponse
    {
        $bookId = $this->repository->getById($book->id);

        return (new BookResource($bookId))->response();
    }
}
