<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\DTO\Author\AuthorResponseDTO;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

final readonly class ShowAuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function execute(int $id): ?AuthorResponseDTO
    {
        return $this->repository->getById($id);
    }
}
