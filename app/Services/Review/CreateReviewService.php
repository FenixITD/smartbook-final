<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\DTO\Review\ReviewDTO;
use App\DTO\Review\ReviewResponseDTO;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final readonly class CreateReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $repository
    ) {}

    public function execute(ReviewDTO $dto): ReviewResponseDTO
    {
        return $this->repository->create([
            'userId' => $dto->userId,
            'bookId' => $dto->bookId,
            'rating' => $dto->rating,
            'comment' => $dto->comment,
        ]);
    }
}
