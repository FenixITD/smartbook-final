<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Dto\Book\BookDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;

class CreateBookService
{
    public function __construct(
        private BookRepositoryInterface $repository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    /**
     * @param array<int> $genreIds
     */
    public function execute(BookDto $dto, array $genreIds): void
    {
        $this->transactionManager->transaction(function () use ($dto, $genreIds): void {
            $book = $this->repository->create($dto);

            $this->repository->syncBookGenres($book->id, $genreIds);
        });
    }
}
