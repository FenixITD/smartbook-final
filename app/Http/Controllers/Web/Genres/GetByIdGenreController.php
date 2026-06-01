<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Genres;

use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\View\View;

final readonly class GetByIdGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $genreId): View
    {
        $genre = $this->repository->findByIdWithRelations($genreId);

        return view('genres.show', compact('genre'));
    }
}
