<?php

declare(strict_types=1);

namespace App\DTO\Review;

use App\Models\Review;

final readonly class ReviewResponseDTO
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $bookId,
        public float $rating,
        public string $comment,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(Review $review): self
    {
        return new self(
            id: $review->id,
            userId: (int) $review->userId,
            bookId: (int) $review->bookId,
            rating: (float) $review->rating,
            comment: $review->comment,
            createdAt: $review->created_at->toDateTimeString(),
            updatedAt: $review->updated_at->toDateTimeString(),
        );
    }
}
