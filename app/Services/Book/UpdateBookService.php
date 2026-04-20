<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Http\Requests\Book\BookWebDataRequest;
use App\Repositories\Interfaces\BookRepositoryInterface;

readonly class UpdateBookService
{
    public function __construct(
        private BookRepositoryInterface $repository)
    {
    }

    public function execute(BookWebDataRequest $request, int $bookId): void
    {
        $book = $this->repository->findModel($bookId);

        $this->repository->update($bookId, $request->toDtoForUpdate($book));

        $this->repository->syncBookGenres($book, $request->genres());
    }
}
