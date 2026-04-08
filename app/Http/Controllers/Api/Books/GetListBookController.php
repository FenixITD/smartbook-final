<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Requests\Book\BookListRequest;
use App\Http\Resources\Book\BookResource;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListBookController
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function __invoke(BookListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $books = $this->repository->getList($filters);

        return BookResource::collection($books)->response();
    }
}
