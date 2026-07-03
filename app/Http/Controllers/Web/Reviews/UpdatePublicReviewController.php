<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Http\Requests\Review\StorePublicReviewRequest;
use App\Services\Review\UpdatePublicReviewService;
use Illuminate\Http\RedirectResponse;

final readonly class UpdatePublicReviewController
{
    public function __construct(
        private UpdatePublicReviewService $service,
    ) {
    }

    public function __invoke(StorePublicReviewRequest $request, int $reviewId): RedirectResponse
    {
        $this->service->execute($reviewId, $request->toDto());

        return redirect()->back()->with('success', 'Your review has been updated!');
    }
}
