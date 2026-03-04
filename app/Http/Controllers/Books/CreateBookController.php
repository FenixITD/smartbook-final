<?php

declare(strict_types=1);

namespace App\Http\Controllers\Books;

use App\DTO\Book\BookDTO;
use App\Http\Requests\BookRequest;
use App\Http\Resources\Book\BookResource;
use App\Services\Book\CreateBookService;
use Illuminate\Http\JsonResponse;

readonly class CreateBookController
{
    public function __construct(
        private CreateBookService $service
    ) {}

    public function __invoke(BookRequest $request): JsonResponse
    {
        $dto = BookDTO::fromRequest($request);
        $book = $this->service->execute($dto);

        return (new BookResource($book))->response()->setStatusCode(201);
    }
}
