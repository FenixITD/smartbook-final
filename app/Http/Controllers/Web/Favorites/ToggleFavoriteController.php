<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Favorites;

use App\Http\Requests\Favorite\FavoriteToggleWebRequest;
use App\Models\User;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\RedirectResponse;

final readonly class ToggleFavoriteController
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository,
    ) {
    }

    public function __invoke(FavoriteToggleWebRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->favoriteRepository->toggle(
            userId: $user->id,
            bookId: $request->integer('book_id'),
        );

        return back();
    }
}
