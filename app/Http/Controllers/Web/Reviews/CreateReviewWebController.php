<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Http\Requests\Review\ReviewDataRequest;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class CreateReviewWebController
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function create(): View
    {
        return view('reviews.create');
    }

    public function store(ReviewDataRequest $request): RedirectResponse
    {
        $this->repository->create($request->toDto());

        return redirect()->route('reviews.index')
            ->with('success', 'Review created successfully.');
    }
}
