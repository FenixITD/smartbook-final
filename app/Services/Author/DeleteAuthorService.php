<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

readonly class DeleteAuthorService
{
    public function __construct(
        private readonly AuthorRepositoryInterface $repository
    ) {}

    public function execute(Author $author): bool
    {
        return $this->repository->delete($author);
    }
}
