<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Dto\Review\ReviewDto;
use App\Jobs\SendEntityNotificationJob;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final readonly class StorePublicReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function execute(ReviewDto $dto): void
    {
        $reviewDto = $this->repository->create($dto);

        SendEntityNotificationJob::dispatch(
            'Review',
            'created',
            [
                'id' => $reviewDto->id,
                'user_name' => $reviewDto->userName,
                'rating' => $reviewDto->rating,
                'comment' => $reviewDto->comment,
            ],
            now()->format('d.m.Y H:i:s')
        );
    }
}
