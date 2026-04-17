<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reviews;

use App\Http\Requests\Review\ReviewListRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ReviewListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $reviews = $this->repository->getList($filters);

        return ReviewResource::collection($reviews)->response();
    }
}
