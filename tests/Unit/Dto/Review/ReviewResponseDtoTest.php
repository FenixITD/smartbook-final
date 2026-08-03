<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Review;

use App\Dto\Review\ReviewResponseDto;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class ReviewResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data_and_user_relation(): void
    {
        $user = new User();
        $user->name = 'Jane Doe';

        $review = new Review();
        $review->id = 25;
        $review->user_id = 1;
        $review->book_id = 10;
        $review->rating = 5.0;
        $review->comment = 'Amazing story!';
        $review->created_at = Carbon::parse('2026-06-01 12:00:00');
        $review->updated_at = Carbon::parse('2026-06-01 12:30:00');

        $review->setRelation('user', $user);

        $dto = ReviewResponseDto::fromModel($review);

        $this->assertSame(25, $dto->id);
        $this->assertSame(1, $dto->userId);
        $this->assertSame(10, $dto->bookId);
        $this->assertSame('Jane Doe', $dto->userName);
        $this->assertSame(5.0, $dto->rating);
        $this->assertSame('Amazing story!', $dto->comment);
        $this->assertSame('2026-06-01 12:00:00', $dto->createdAt);
        $this->assertSame('2026-06-01 12:30:00', $dto->updatedAt);
    }

    public function test_from_model_creates_dto_with_null_fields_and_missing_user(): void
    {
        $review = new Review();
        $review->id = 26;
        $review->user_id = 2;
        $review->book_id = 11;
        $review->rating = 3.0;
        $review->comment = null;
        $review->created_at = null;
        $review->updated_at = null;

        $review->setRelation('user', null);

        $dto = ReviewResponseDto::fromModel($review);

        $this->assertSame(26, $dto->id);
        $this->assertSame('Unknown', $dto->userName);
        $this->assertSame('', $dto->comment);
        $this->assertSame('', $dto->createdAt);
        $this->assertSame('', $dto->updatedAt);
    }
}
