<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Http\Requests\Author\AuthorListRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Services\Author\SearchAuthorService;
use App\Traits\HandlesWebListSearch;
use Illuminate\View\View;

final readonly class GetListAuthorController
{
    use HandlesWebListSearch;

    public function __construct(
        private AuthorRepositoryInterface $repository,
        private SearchAuthorService $searchService,
    ) {
    }

    public function __invoke(AuthorListRequest $request): View
    {
        $paginated = $this->handleWebListSearch($request->toDto(), $this->repository, $this->searchService);

        return view('authors.list', compact('paginated'));
    }
}
