<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\DTO\AuthorDTO;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

readonly class CreateAuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function execute(AuthorDTO $dto): Author
    {
        return $this->repository->create([
            'name' => $dto->name,
        ]);
    }
}
