<?php

declare(strict_types=1);

namespace App\Http\Controllers\Books;

use App\Http\Requests\Book\BookDataRequest;
use App\Http\Resources\Book\BookResource;
use App\Services\Book\CreateBookService;
use Illuminate\Http\JsonResponse;

readonly class CreateBookController
{
    public function __construct(
        private CreateBookService $service
    ) {}

    public function __invoke(BookDataRequest $request): JsonResponse
    {
        $book = $this->service->execute($request->toDto());

        return (new BookResource($book))->response()->setStatusCode(201);
    }
}
