<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\DTO\Review\ReviewFiltersDTO;
use App\DTO\Review\ReviewResponseDTO;
use App\Models\Review;

interface ReviewRepositoryInterface
{
    /**
     * @return array<ReviewResponseDTO>
     */
    public function getList(ReviewFiltersDTO $filters): array;

    public function getById(int $id): ?ReviewResponseDTO;

    public function create(array $data): ReviewResponseDTO;

    public function update(Review $review, array $data): ?ReviewResponseDTO;

    public function delete(Review $review): bool;
}
