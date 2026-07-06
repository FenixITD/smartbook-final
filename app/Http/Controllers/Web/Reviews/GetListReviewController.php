<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Http\Requests\Review\ReviewListRequest;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\Review\SearchReviewService;
use App\Traits\HandlesWebListSearch;
use Illuminate\View\View;

final readonly class GetListReviewController
{
    use HandlesWebListSearch;

    public function __construct(
        private ReviewRepositoryInterface $repository,
        private SearchReviewService $searchService,
    ) {
    }

    public function __invoke(ReviewListRequest $request): View
    {
        $paginated = $this->handleWebListSearch($request->toDto(), $this->repository, $this->searchService);

        return view('reviews.list', compact('paginated'));
    }
}
