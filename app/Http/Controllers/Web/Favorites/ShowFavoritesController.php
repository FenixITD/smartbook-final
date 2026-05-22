<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Favorites;

use App\Http\Requests\Favorite\FavoriteShowWebRequest;
use App\Services\Favorite\FavoriteService;
use Illuminate\View\View;

final readonly class ShowFavoritesController
{
    public function __construct(
        private FavoriteService $favoriteService,
    ) {
    }

    public function __invoke(FavoriteShowWebRequest $request): View
    {
        $books = $this->favoriteService->getBooksByUser(
            userId: (int) $request->user()->id,
            filters: $request->toDto(),
        );

        return view('favorites.index', compact('books'));
    }
}
