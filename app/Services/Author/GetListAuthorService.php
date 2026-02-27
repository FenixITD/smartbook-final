<?php

namespace App\Services\Author;

use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

readonly class GetListAuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function execute(Request $request): LengthAwarePaginator
    {
        return $this->repository->getList($request);
    }
}
