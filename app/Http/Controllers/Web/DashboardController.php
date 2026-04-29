<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Requests\Dashboard\DashboardListRequest;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\View\View;

final readonly class DashboardController
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private GenreRepositoryInterface $genreRepository,
    ) {
    }

    public function __invoke(DashboardListRequest $request): View
    {
        $paginated = $this->bookRepository->getDashboardList($request->toDto());
        $genres = $this->genreRepository->getAll();

        return view('dashboard', compact('paginated', 'genres'));
    }
}
