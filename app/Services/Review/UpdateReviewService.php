<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\DTO\Review\ReviewDto;
use App\DTO\Review\ReviewResponseDto;
use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final readonly class UpdateReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $repository
    ) {}

    public function execute(Review $review, ReviewDto $dto): ReviewResponseDto
    {
        return $this->repository->update($review, $dto);
    }
}
