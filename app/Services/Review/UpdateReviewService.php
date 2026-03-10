<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\DTO\Review\ReviewDTO;
use App\DTO\Review\ReviewResponseDTO;
use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final readonly class UpdateReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $repository
    ) {}

    public function execute(Review $review, ReviewDTO $dto): ReviewResponseDTO
    {
        $this->repository->update($review, [
            'userId' => $dto->userId,
            'bookId' => $dto->bookId,
            'rating' => $dto->rating,
            'comment' => $dto->comment,
        ]);

        return ReviewResponseDTO::fromModel($review->fresh());
    }
}
