<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Genres;

use App\Http\Requests\Genre\GenreDataRequest;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class UpdateGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function edit(int $genreId): View
    {
        $genre = $this->repository->findByIdWithRelations($genreId);

        return view('genres.edit', compact('genre'));
    }

    public function update(GenreDataRequest $request, int $genreId): RedirectResponse
    {
        $this->repository->update($genreId, $request->toDto());

        return redirect()->route('genres.index')
            ->with('success', 'Genre updated successfully.');
    }
}
