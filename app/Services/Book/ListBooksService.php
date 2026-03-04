<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\DTO\Book\BookFiltersDTO;
use App\DTO\Book\BookResponseDTO;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class ListBooksService
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    /**
     * @return array<BookResponseDTO>
     */
    public function execute(BookFiltersDTO $filters): array
    {
        return $this->repository->getList($filters);
    }
}
