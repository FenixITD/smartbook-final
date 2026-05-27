<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Http\Requests\Review\StorePublicReviewRequest;
use App\Repositories\Eloquent\ReviewRepository;
use Illuminate\Http\RedirectResponse;

final readonly class StorePublicReviewController
{
    public function __construct(
        private ReviewRepository $repository,
    ) {
    }

    public function __invoke(StorePublicReviewRequest $request): RedirectResponse
    {
        $this->repository->create($request->toDto());

        return redirect()->back()->with('success', 'Your review has been submitted!');
    }
}
