<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Requests\Dashboard\DashboardListRequest;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Book\GetDashboardBooksService;
use Illuminate\View\View;

final readonly class DashboardController
{
    public function __construct(
        private GetDashboardBooksService $dashboardBooksService,
        private GenreRepositoryInterface $genreRepository,
        private FavoriteRepositoryInterface $favoriteRepository,
    ) {
    }

    public function __invoke(DashboardListRequest $request): View
    {
        \Log::info('Dashboard search', [
            'all' => $request->all(),
            'dto' => $request->toDto(),
        ]);

        $paginated = $this->dashboardBooksService->get($request->toDto());
        $genres = $this->genreRepository->getAll();
        $favoriteBookIds = auth()->check() ? $this->favoriteRepository->getBookIdsByUser((int) auth()->id()) : [];

        return view('dashboard', compact('paginated', 'genres', 'favoriteBookIds'));
    }
}
