<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\DTO\Author\AuthorDto;
use App\DTO\Author\AuthorResponseDto;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

final readonly class UpdateAuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function execute(Author $author, AuthorDto $dto): AuthorResponseDto
    {
        return $this->repository->update($author, $dto);
    }
}
