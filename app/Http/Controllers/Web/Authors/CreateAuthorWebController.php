<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Http\Requests\Author\AuthorDataRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class CreateAuthorWebController
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function create(): View
    {
        return view('authors.create');
    }

    public function store(AuthorDataRequest $request): RedirectResponse
    {
        $this->repository->create($request->toDto());

        return redirect()->route('authors.index')
            ->with('success', 'Author created successfully.');
    }
}
