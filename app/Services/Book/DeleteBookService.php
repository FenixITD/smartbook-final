<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteBookService
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private ReviewRepositoryInterface $reviewRepository,
        private OrderItemRepositoryInterface $orderItemRepository,
        private TransactionManagerInterface $transactionManager,
    ) {}

    public function execute(int $bookId): void
    {
        $coverPath = null;

        $this->transactionManager->transaction(function () use ($bookId, &$coverPath): void {
            if ($this->orderItemRepository->existsByBookId($bookId)) {
                throw ValidationException::withMessages([
                    'book' => 'This book has been ordered and cannot be deleted.',
                ]);
            }

            foreach ($this->reviewRepository->getModelsByBookId($bookId) as $review) {
                $review->delete();
            }

            $book = $this->bookRepository->getById($bookId);

            if ($book !== null && $book->coverImage !== null && $book->coverImage !== '') {
                $coverPath = $book->coverImage;
            }

            $this->bookRepository->delete($bookId);
        });

        if (is_string($coverPath)) {
            Storage::disk('s3')->delete($coverPath);
        }
    }
}
