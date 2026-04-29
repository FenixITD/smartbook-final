<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Http\Requests\Author\AuthorDataRequest;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class UpdateAuthorController
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function edit(Author $author): View
    {
        return view('authors.edit', compact('author'));
    }

    public function update(AuthorDataRequest $request, int $authorId): RedirectResponse
    {
        $this->repository->update($authorId, $request->toDto());

        return redirect()->route('authors.index')
            ->with('success', 'Author updated successfully.');
    }
}
