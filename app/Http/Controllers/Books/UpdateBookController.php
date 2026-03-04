<?php

declare(strict_types=1);

namespace App\Http\Controllers\Books;

use App\DTO\Book\BookDTO;
use App\Http\Requests\BookRequest;
use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use App\Services\Book\UpdateBookService;
use Illuminate\Http\JsonResponse;

readonly class UpdateBookController
{
    public function __construct(
        private UpdateBookService $service
    ) {}

    public function __invoke(BookRequest $request, Book $book): JsonResponse
    {
        $dto = BookDTO::fromRequest($request);
        $updated = $this->service->execute($book, $dto);

        return (new BookResource($updated))->response();
    }
}
