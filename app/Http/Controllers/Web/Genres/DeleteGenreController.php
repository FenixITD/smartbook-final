<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Genres;

use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\RedirectResponse;

final readonly class DeleteGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $genreId): RedirectResponse
    {
        $this->repository->delete($genreId);

        return redirect()->route('genres.index')
            ->with('success', 'Genre deleted successfully.');
    }
}
