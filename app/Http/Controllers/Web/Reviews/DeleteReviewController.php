<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\RedirectResponse;

final readonly class DeleteReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $reviewId): RedirectResponse
    {
        $this->repository->delete($reviewId);

        return redirect()->route('reviews.index')
            ->with('success', 'Review deleted successfully.');
    }
}
