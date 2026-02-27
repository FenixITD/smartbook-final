<?php

namespace App\Services\Author;

use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

readonly class GetByIdAuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function execute(int $id): ?Author
    {
        return $this->repository->getById($id);
    }
}
