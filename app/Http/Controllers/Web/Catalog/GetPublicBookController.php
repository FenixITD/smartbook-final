<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Catalog;

use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
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

        $reviews = $this->reviewRepository->getByBookId($book->id);

        return view('catalog.show', compact('book', 'reviews'));
    }
}
