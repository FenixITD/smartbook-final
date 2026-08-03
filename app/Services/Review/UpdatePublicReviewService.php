<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Dto\Review\ReviewDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final readonly class UpdatePublicReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {}

    public function execute(int $reviewId, ReviewDto $dto): void
    {
        $review = $this->repository->getById($reviewId);

        if ($review === null || $review->userId !== $dto->userId) {
            abort(403, 'Unauthorized action.');
        }

        $dto = new ReviewDto(
            userId: $dto->userId,
            bookId: $review->bookId,
            rating: $dto->rating,
            comment: $dto->comment,
        );

        $this->repository->update($reviewId, $dto);
    }
}
