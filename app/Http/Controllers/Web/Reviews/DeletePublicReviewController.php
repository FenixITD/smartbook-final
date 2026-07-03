<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Services\Review\DeletePublicReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final readonly class DeletePublicReviewController
{
    public function __construct(
        private DeletePublicReviewService $service,
    ) {
    }

    public function __invoke(int $reviewId): RedirectResponse
    {
        $this->service->execute($reviewId, (int) Auth::id());

        return redirect()->back()->with('success', 'Your review has been deleted.');
    }
}
