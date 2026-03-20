<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorFiltersDto;
use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Services\Author\CreateAuthorService;
use App\Services\Author\UpdateAuthorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AuthorWebController extends Controller
{
    public function __construct(
        private readonly AuthorRepositoryInterface $repository,
        private readonly CreateAuthorService $createService,
        private readonly UpdateAuthorService $updateService,
    ) {}

    public function index(Request $request): View
    {
        $filters = new AuthorFiltersDto(
            search: $request->input('search'),
            perPage: (int) $request->input('perPage', 15),
            sortBy: $request->input('sortBy', 'id'),
            sortDirection: $request->input('sortDirection', 'asc'),
        );

        $authors = Author::query()
            ->when($filters->search, fn ($q) => $q->where('name', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return view('authors.list', compact('authors'));
    }

    public function create(): View
    {
        return view('authors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->createService->execute(new AuthorDto(name: $validated['name']));

        return redirect()->route('authors.index')
            ->with('success', 'Author created successfully.');
    }

    public function show(Author $author): View
    {
        return view('authors.show', compact('author'));
    }

    public function edit(Author $author): View
    {
        return view('authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->updateService->execute($author, new AuthorDto(name: $validated['name']));

        return redirect()->route('authors.index')
            ->with('success', 'Author updated successfully.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        $this->repository->delete($author);

        return redirect()->route('authors.index')
            ->with('success', 'Author deleted successfully.');
    }
}
