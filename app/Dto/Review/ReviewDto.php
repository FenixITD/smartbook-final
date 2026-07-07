<?php

declare(strict_types=1);

namespace App\Dto\Review;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class ReviewDto implements Arrayable
{
    public function __construct(
        public int $userId,
        public int $bookId,
        public float $rating,
        public string $comment,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'book_id' => $this->bookId,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ];
    }
}
