<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\View\View;

final readonly class GetByIdReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $reviewId): View
    {
        $review = $this->repository->findByIdWithRelations($reviewId);

        return view('reviews.show', compact('review'));
    }
}
