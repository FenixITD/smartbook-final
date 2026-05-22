<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Http\Requests\Review\ReviewListRequest;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\Review\GetWebListReviewService;
use Illuminate\View\View;

final readonly class GetListReviewController
{
    public function __construct(
        private GetWebListReviewService $getWebListReviewService,
    ) {}

    public function __invoke(ReviewListRequest $request): View
    {
        $paginated = $this->getWebListReviewService->get($request->toDto());

        return view('reviews.list', compact('paginated'));
    }
}
