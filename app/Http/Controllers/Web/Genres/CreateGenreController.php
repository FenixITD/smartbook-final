<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Genres;

use App\Http\Requests\Genre\GenreDataRequest;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class CreateGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function create(): View
    {
        return view('genres.create');
    }

    public function store(GenreDataRequest $request): RedirectResponse
    {
        $this->repository->create($request->toDto());

        return redirect()->route('genres.index')
            ->with('success', 'Genre created successfully.');
    }
}
