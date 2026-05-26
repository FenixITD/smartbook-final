<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Favorites;

use App\Dto\Favorite\FavoriteFiltersDto;
use App\Services\Favorite\FavoriteService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class ShowFavoritesController
{
    public function __construct(
        private FavoriteService $favoriteService,
    ) {
    }

    public function __invoke(Request $request): View
    {
        $books = $this->favoriteService->getBooksByUser(
            userId: (int) $request->user()->id,
            filters: new FavoriteFiltersDto(perPage: 18),
        );

        return view('favorites.index', compact('books'));
    }
}
