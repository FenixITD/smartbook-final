<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Genres;

use App\Http\Requests\Genre\GenreListRequest;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Genre\GetWebListGenreService;
use Illuminate\View\View;

final readonly class GetListGenreController
{
    public function __construct(
        private GetWebListGenreService $getWebListGenreService,
    ) {}

    public function __invoke(GenreListRequest $request): View
    {
        $paginated = $this->getWebListGenreService->get($request->toDto());

        return view('genres.list', compact('paginated'));
    }
}
