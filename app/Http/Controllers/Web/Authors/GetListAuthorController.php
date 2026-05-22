<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Http\Requests\Author\AuthorListRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Services\Author\GetWebListAuthorService;
use Illuminate\View\View;

final readonly class GetListAuthorController
{
    public function __construct(
        private readonly GetWebListAuthorService $getWebListAuthorService,
    ) {}

    public function __invoke(AuthorListRequest $request): View
    {
        $paginated = $this->getWebListAuthorService->get($request->toDto());

        return view('authors.list', compact('paginated'));
    }
}
