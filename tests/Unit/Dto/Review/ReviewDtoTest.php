<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Review;

use App\Dto\Review\ReviewDto;
use Tests\TestCase;

final class ReviewDtoTest extends TestCase
{
    public function test_review_dto_initializes_and_returns_array(): void
    {
        $dto = new ReviewDto(
            userId: 1,
            bookId: 10,
            rating: 4.5,
            comment: 'Great book, highly recommend!'
        );

        $this->assertSame(1, $dto->userId);
        $this->assertSame(10, $dto->bookId);
        $this->assertSame(4.5, $dto->rating);
        $this->assertSame('Great book, highly recommend!', $dto->comment);

        $this->assertSame([
            'user_id' => 1,
            'book_id' => 10,
            'rating' => 4.5,
            'comment' => 'Great book, highly recommend!',
        ], $dto->toArray());
    }
}
