<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Genres;

use App\Http\Requests\Genre\GenreListRequest;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Genre\SearchGenreService;
use App\Traits\HandlesWebListSearch;
use Illuminate\View\View;

final readonly class GetListGenreController
{
    use HandlesWebListSearch;

    public function __construct(
        private GenreRepositoryInterface $repository,
        private SearchGenreService $searchService,
    ) {
    }

    public function __invoke(GenreListRequest $request): View
    {
        $paginated = $this->handleWebListSearch($request->toDto(), $this->repository, $this->searchService);

        return view('genres.list', compact('paginated'));
    }
}
