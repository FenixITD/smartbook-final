<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Requests\Book\BookDataRequest;
use App\Http\Resources\Book\BookResource;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\JsonResponse;

readonly class CreateBookController
{
    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
    }

    public function __invoke(BookDataRequest $request): JsonResponse
    {
        $book = $this->repository->create($request->toDto());

        return (new BookResource($book))->response()->setStatusCode(201);
    }
}
