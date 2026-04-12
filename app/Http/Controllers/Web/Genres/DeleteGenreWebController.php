<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Genres;

use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\RedirectResponse;

final readonly class DeleteGenreWebController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {}

    public function __invoke(int $genre): RedirectResponse
    {
        $this->repository->delete($genre);

        return redirect()->route('genres.index')
            ->with('success', 'Genre deleted successfully.');
    }
}
