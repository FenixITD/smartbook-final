<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Http\Requests\Author\AuthorDataRequest;
use App\Services\Author\CreateAuthorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class CreateAuthorWebController
{
    public function __construct(
        private CreateAuthorService $service,
    ) {}

    public function create(): View
    {
        return view('authors.create');
    }

    public function store(AuthorDataRequest $request): RedirectResponse
    {
        $this->service->execute($request->toDto());

        return redirect()->route('authors.index')
            ->with('success', 'Author created successfully.');
    }
}
