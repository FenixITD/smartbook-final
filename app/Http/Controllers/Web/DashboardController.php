<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Requests\Dashboard\DashboardListRequest;
use App\Models\Genre;
use App\Repositories\Interfaces\DashboardRepositoryInterface;
use Illuminate\View\View;

final readonly class DashboardController
{
    public function __construct(
        private DashboardRepositoryInterface $repository,
    ) {}

    public function __invoke(DashboardListRequest $request): View
    {
        $paginated = $this->repository->getDashboardList($request->toDto());
        $genres = Genre::orderBy('name')->get();

        return view('dashboard', compact('paginated', 'genres'));
    }
}
