<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Requests\Dashboard\DashboardListRequest;
use App\Repositories\Interfaces\DashboardRepositoryInterface;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\View\View;

final readonly class DashboardController
{
    public function __construct(
        private DashboardRepositoryInterface $dashboardRepository,
        private GenreRepositoryInterface $genreRepository,
    ) {
    }

    public function __invoke(DashboardListRequest $request): View
    {
        $paginated = $this->dashboardRepository->getDashboardList($request->toDto());
        $genres = $this->genreRepository->all();

        return view('dashboard', compact('paginated', 'genres'));
    }
}
