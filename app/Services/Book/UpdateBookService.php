<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\DTO\Book\BookDto;
use App\DTO\Book\BookResponseDto;
use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class UpdateBookService
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function execute(Book $book, BookDto $dto): BookResponseDto
    {
        return $this->repository->update($book, $dto);
    }
}
