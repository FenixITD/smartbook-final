<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Http\Requests\Review\ReviewDataRequest;
use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class UpdateReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function edit(int $reviewId): View
    {
        $review = $this->repository->findByIdWithRelations($reviewId);

        return view('reviews.edit', compact('review'));
    }

    public function update(ReviewDataRequest $request, int $review): RedirectResponse
    {
        $this->repository->update($review, $request->toDto());

        return redirect()->route('reviews.index')
            ->with('success', 'Review updated successfully.');
    }
}
