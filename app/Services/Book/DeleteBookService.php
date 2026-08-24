<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class DeleteBookService
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private ReviewRepositoryInterface $reviewRepository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function execute(int $bookId): void
    {
        $this->transactionManager->transaction(function () use ($bookId): void {
            foreach ($this->reviewRepository->getModelsByBookId($bookId) as $review) {
                $review->delete();
            }

            $book = $this->bookRepository->getById($bookId);

            if ($book !== null && $book->coverImage !== null && $book->coverImage !== '') {
                Storage::disk('s3')->delete($book->coverImage);
            }

            $this->bookRepository->delete($bookId);
        });
    }
}
