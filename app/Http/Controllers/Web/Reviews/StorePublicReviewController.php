<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Http\Requests\Review\StorePublicReviewRequest;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\RedirectResponse;

final readonly class StorePublicReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function __invoke(StorePublicReviewRequest $request): RedirectResponse
    {
        $this->repository->create($request->toDto());

        return redirect()->back()->with('success', 'Your review has been submitted!');
    }
}
