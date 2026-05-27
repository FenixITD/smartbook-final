<?php

declare(strict_types=1);

namespace App\Dto\Review;

use App\Models\Review;

final readonly class BookReviewResponseDto
{
    public function __construct(
        public int $id,
        public string $userName,
        public float $rating,
        public string $comment,
        public string $createdAt,
    ) {
    }

    public static function fromModel(Review $review): self
    {
        return new self(
            id: $review->id,
            userName: $review->user->name ?? 'Unknown',
            rating: $review->rating,
            comment: $review->comment ?? '',
            createdAt: $review->created_at?->toDateTimeString() ?? '',
        );
    }
}
