<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Http\Requests\Author\AuthorListRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\View\View;

final readonly class GetListAuthorWebController
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function __invoke(AuthorListRequest $request): View
    {
        $filters = $request->toDto();
        $authors = $this->repository->getWebList($filters);

        return view('authors.list', compact('authors'));
    }
}
