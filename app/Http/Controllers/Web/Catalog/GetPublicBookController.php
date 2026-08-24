<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Catalog;

use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final readonly class GetPublicBookController
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private ReviewRepositoryInterface $reviewRepository,
    ) {
    }

    public function __invoke(string $slug): View
    {
        $book = $this->bookRepository->findBySlugWithRelations($slug);

        if ($book->status !== 'active' && Auth::user()?->role !== 'admin') {
            abort(404);
        }

        $reviews = $this->reviewRepository->getByBookId($book->id);

        $userReview = null;
        if (Auth::check()) {
            $userReview = $this->reviewRepository->findByUserAndBook((int) Auth::id(), $book->id);
        }

        return view('catalog.show', compact('book', 'reviews', 'userReview'));
    }
}
