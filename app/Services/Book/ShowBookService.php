<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\DTO\Book\BookResponseDTO;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class ShowBookService
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function execute(int $id): ?BookResponseDTO
    {
        return $this->repository->getById($id);
    }
}
