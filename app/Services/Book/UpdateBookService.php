<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Dto\Book\BookDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class UpdateBookService
{
    public function __construct(
        private BookRepositoryInterface $repository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    /** @param array<int> $genreIds */
    public function execute(int $bookId, BookDto $dto, array $genreIds): void
    {
        $this->transactionManager->transaction(function () use ($bookId, $dto, $genreIds): void {
            $this->repository->update($bookId, $dto);

            $this->repository->syncBookGenres($bookId, $genreIds);
        });
    }
}
