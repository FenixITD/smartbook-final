<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Favorites;

use App\Http\Requests\Favorite\FavoriteShowWebRequest;
use App\Models\User;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\View\View;

final readonly class ShowFavoritesController
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository,
        private BookRepositoryInterface $bookRepository,
    ) {
    }

    public function __invoke(FavoriteShowWebRequest $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $userId = $user->id;

        $favoriteBookIds = $this->favoriteRepository->getBookIdsByUser($userId);
        $books = $favoriteBookIds !== [] ? $this->bookRepository->getByIdsWithAuthor($favoriteBookIds, 18) : null;

        return view('favorites.index', compact('books'));
    }
}
