<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Dto\Review\ReviewDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final class StorePublicReviewService
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepository,
    ) {
    }

    /**
     * @param ReviewDto $dto
     * @return void
     *
     * Stores a newly submitted public review in the database.
     */
    public function store(ReviewDto $dto): void
    {
        $this->reviewRepository->create($dto);
    }
}
