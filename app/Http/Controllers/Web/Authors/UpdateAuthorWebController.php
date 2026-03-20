<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Dto\Author\AuthorDto;
use App\Models\Author;
use App\Services\Author\UpdateAuthorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class UpdateAuthorWebController
{
    public function __construct(
        private UpdateAuthorService $service,
    ) {}

    public function edit(Author $author): View
    {
        return view('authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->service->execute($author, new AuthorDto(name: $validated['name']));

        return redirect()->route('authors.index')
            ->with('success', 'Author updated successfully.');
    }
}
