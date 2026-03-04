<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class DeleteBookService
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function execute(Book $book): bool
    {
        return $this->repository->delete($book);
    }
}
