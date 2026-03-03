<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\DTO\Author\AuthorDTO;
use App\DTO\Author\AuthorResponseDTO;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

final readonly class UpdateAuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function execute(Author $author, AuthorDTO $dto): AuthorResponseDTO
    {
        $this->repository->update($author, [
            'name' => $dto->name,
        ]);

        return AuthorResponseDTO::fromModel($author->fresh());
    }
}
