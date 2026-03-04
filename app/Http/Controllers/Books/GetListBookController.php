<?php

declare(strict_types=1);

namespace App\Http\Controllers\Books;

use App\DTO\Book\BookFiltersDTO;
use App\Http\Requests\BookRequest;
use App\Http\Resources\Book\BookCollection;
use App\Services\Book\ListBooksService;
use Illuminate\Http\JsonResponse;

final readonly class GetListBookController
{
    public function __construct(
        private ListBooksService $service
    ) {}

    public function __invoke(BookRequest $request): JsonResponse
    {
        $filters = BookFiltersDTO::fromRequest($request);
        $books = $this->service->execute($filters);

        return (new BookCollection($books))->response();
    }
}
