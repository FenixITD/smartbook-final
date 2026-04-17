<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Genres;

use App\Http\Requests\Genre\GenreListRequest;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\View\View;

final readonly class GetListGenreWebController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function __invoke(GenreListRequest $request): View
    {
        $paginated = $this->repository->getWebList($request->toDto());

        return view('genres.list', compact('paginated'));
    }
}
