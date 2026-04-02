<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\DTO\Book\BookDto;
use App\DTO\Book\BookResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class CreateBookService
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function execute(BookDto $dto): BookResponseDto
    {
        return $this->repository->create($dto);
    }
}
