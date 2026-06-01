<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Review;

use App\Dto\Review\BookReviewResponseDto;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class BookReviewResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data(): void
    {
        $user = new User();
        $user->name = 'John Smith';

        $review = new Review();
        $review->id = 42;
        $review->rating = 4.0;
        $review->comment = 'Nice plot twists.';
        $review->created_at = Carbon::parse('2026-05-20 15:00:00');

        $review->setRelation('user', $user);

        $dto = BookReviewResponseDto::fromModel($review);

        $this->assertSame(42, $dto->id);
        $this->assertSame('John Smith', $dto->userName);
        $this->assertSame(4.0, $dto->rating);
        $this->assertSame('Nice plot twists.', $dto->comment);
        $this->assertSame('2026-05-20 15:00:00', $dto->createdAt);
    }

    public function test_from_model_handles_missing_user_and_null_fields(): void
    {
        $review = new Review();
        $review->id = 43;
        $review->rating = 2.5;
        $review->comment = null;
        $review->created_at = null;

        $review->setRelation('user', null);

        $dto = BookReviewResponseDto::fromModel($review);

        $this->assertSame(43, $dto->id);
        $this->assertSame('Unknown', $dto->userName);
        $this->assertSame('', $dto->comment);
        $this->assertSame('', $dto->createdAt);
    }
}
