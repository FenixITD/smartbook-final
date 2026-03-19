<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\DTO\Author\AuthorDto;
use App\DTO\Author\AuthorResponseDto;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

readonly class CreateAuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function execute(AuthorDto $dto): AuthorResponseDto
    {
        return $this->repository->create($dto);
    }
}
