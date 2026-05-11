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

    public function __invoke(int $bookId): View
    {
        $book = $this->bookRepository->findByIdWithRelations($bookId);
        $reviews = $this->reviewRepository->getByBookId($bookId);

        return view('catalog.show', compact('book', 'reviews'));
    }
}
