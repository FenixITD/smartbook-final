<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Http\Requests\Book\BookWebDataRequest;
use App\Repositories\Interfaces\BookRepositoryInterface;

readonly class BookCreatorService
{
    public function __construct(
        private BookRepositoryInterface $repository)
    {
    }

    public function execute(BookWebDataRequest $request): void
    {
        $responseDto = $this->repository->create($request->toDto());

        $book = $this->repository->findModel($responseDto->id);

        $this->repository->syncBookGenres($book, $request->genres());
    }
}
