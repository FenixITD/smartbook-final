<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteBookService
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function execute(int $bookId): void
    {
        $this->transactionManager->transaction(function () use ($bookId): void {
            $book = $this->bookRepository->findModel($bookId);

            if ($book->cover_image !== null && $book->cover_image !== '') {
                Storage::disk('public')->delete($book->cover_image);
            }

            $this->bookRepository->delete($bookId);
        });
    }
}
