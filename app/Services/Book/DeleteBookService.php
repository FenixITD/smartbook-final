<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteBookService
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private ConnectionInterface $db,
    ) {}

    public function execute(int $bookId): void
    {
        $this->db->transaction(function () use ($bookId): void {
            $book = $this->bookRepository->findModel($bookId);

            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }

            $this->bookRepository->delete($bookId);
        });
    }
}
