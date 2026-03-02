<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\DTO\AuthorFiltersDTO;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

readonly class GetListAuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function execute(AuthorFiltersDTO $filters): LengthAwarePaginator
    {
        return $this->repository->getList($filters);
    }
}
