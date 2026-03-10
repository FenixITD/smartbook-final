<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\DTO\Review\ReviewFiltersDTO;
use App\Http\Requests\Review\ReviewListRequest;
use App\Http\Resources\Review\ReviewCollection;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository
    ) {}

    public function __invoke(ReviewListRequest $request): JsonResponse
    {
        $filters = ReviewFiltersDTO::fromRequest($request);
        $reviews = $this->repository->getList($filters);

        return (new ReviewCollection($reviews))->response();
    }
}
