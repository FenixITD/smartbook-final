<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Http\Requests\Review\ReviewListRequest;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\View\View;

final readonly class GetListReviewWebController
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {}

    public function __invoke(ReviewListRequest $request): View
    {
        $paginated = $this->repository->getWebList($request->toDto());

        return view('reviews.list', compact('paginated'));
    }
}
