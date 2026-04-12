<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\RedirectResponse;

final readonly class DeleteReviewWebController
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {}

    public function __invoke(int $review): RedirectResponse
    {
        $this->repository->delete($review);

        return redirect()->route('reviews.index')
            ->with('success', 'Review deleted successfully.');
    }
}
