<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Resources\Book\BookResource;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetBookController
{
    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $bookId): JsonResponse
    {
        $book = $this->repository->getById($bookId);

        return (new BookResource($book))->response();
    }
}
