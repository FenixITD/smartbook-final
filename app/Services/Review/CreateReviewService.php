<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\DTO\Review\ReviewDto;
use App\DTO\Review\ReviewResponseDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final readonly class CreateReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $repository
    ) {}

    public function execute(ReviewDto $dto): ReviewResponseDto
    {
        return $this->repository->create($dto);
    }
}
