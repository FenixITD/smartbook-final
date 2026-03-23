<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Review\ReviewDto;
use App\DTO\Review\ReviewFiltersDto;
use App\DTO\Review\ReviewResponseDto;
use App\Models\Review;

interface ReviewRepositoryInterface
{
    /**
     * @return array<ReviewResponseDto>
     */
    public function getList(ReviewFiltersDto $filters): array;

    public function getById(int $id): ?ReviewResponseDto;

    public function create(ReviewDto $data): ReviewResponseDto;

    public function update(Review $review, ReviewDto $data): ?ReviewResponseDto;

    public function delete(Review $review): bool;
}
