<?php

declare(strict_types=1);

namespace App\Http\Controllers\Books;

use App\DTO\Book\BookFiltersDTO;
use App\Http\Requests\Book\BookListRequest;
use App\Http\Resources\Book\BookCollection;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListBookController
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function __invoke(BookListRequest $request): JsonResponse
    {
        $filters = BookFiltersDTO::fromRequest($request);
        $books = $this->repository->getList($filters);

        return (new BookCollection($books))->response();
    }
}
