<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\DTO\Author\AuthorDTO;
use App\DTO\Author\AuthorResponseDTO;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

readonly class CreateAuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function execute(AuthorDTO $dto): AuthorResponseDTO
    {
        return $this->repository->create([
            'name' => $dto->name,
        ]);
    }
}
