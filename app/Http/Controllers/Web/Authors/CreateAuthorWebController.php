<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Dto\Author\AuthorDto;
use App\Services\Author\CreateAuthorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->service->execute(new AuthorDto(name: $validated['name']));

        return redirect()->route('authors.index')
            ->with('success', 'Author created successfully.');
    }
}
