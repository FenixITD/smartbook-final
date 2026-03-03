<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\DTO\Author\AuthorFiltersDTO;
use App\DTO\Author\AuthorResponseDTO;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

final readonly class ListAuthorsService
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    /**
     * @return array<AuthorResponseDTO>
     */
    public function execute(AuthorFiltersDTO $filters): array
    {
        return $this->repository->getList($filters);
    }
}
