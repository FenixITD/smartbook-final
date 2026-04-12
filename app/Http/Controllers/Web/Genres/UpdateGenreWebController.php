<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Genres;

use App\Http\Requests\Genre\GenreDataRequest;
use App\Models\Genre;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class UpdateGenreWebController
{
    public function __construct(
        private GenreRepositoryInterface $repository
    ) {}

    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    public function update(GenreDataRequest $request, int $genre): RedirectResponse
    {
        $this->repository->update($genre, $request->toDto());

        return redirect()->route('genres.index')
            ->with('success', 'Genre updated successfully.');
    }
}
