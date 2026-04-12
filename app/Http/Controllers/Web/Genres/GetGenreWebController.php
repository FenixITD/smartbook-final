<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Genres;

use App\Models\Genre;
use Illuminate\View\View;

final readonly class GetGenreWebController
{
    public function __invoke(Genre $genre): View
    {
        return view('genres.show', compact('genre'));
    }
}
