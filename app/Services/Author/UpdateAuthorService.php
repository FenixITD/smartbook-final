<?php

namespace App\Services\Author;

use App\DTO\AuthorDTO;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

readonly class UpdateAuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function execute(Author $author, AuthorDTO $dto): Author
    {
        $this->repository->update($author, [
            'name' => $dto->name,
        ]);

        return $author->fresh();
    }
}
