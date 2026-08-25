<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Http\Requests\Review\ReviewDataRequest;
use App\Services\Review\StorePublicReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class CreateReviewController
{
    public function __construct(
        private StorePublicReviewService $service,
    ) {
    }

    public function create(): View
    {
        return view('reviews.create');
    }

    public function store(ReviewDataRequest $request): RedirectResponse
    {
        $this->service->execute($request->toDto());

        return redirect()->route('reviews.index')
            ->with('success', 'Review created successfully.');
    }
}
