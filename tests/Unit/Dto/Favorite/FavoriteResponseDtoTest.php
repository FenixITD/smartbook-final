<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Favorite;

use App\Dto\Favorite\FavoriteResponseDto;
use App\Models\Favorite;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class FavoriteResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data(): void
    {
        $favorite = new Favorite();
        $favorite->id = 101;
        $favorite->user_id = 12;
        $favorite->book_id = 55;
        $favorite->created_at = Carbon::parse('2026-06-01 12:00:00');
        $favorite->updated_at = Carbon::parse('2026-06-02 15:30:00');

        $dto = FavoriteResponseDto::fromModel($favorite);

        $this->assertSame(101, $dto->id);
        $this->assertSame(12, $dto->userId);
        $this->assertSame(55, $dto->bookId);
        $this->assertSame('2026-06-01 12:00:00', $dto->createdAt);
        $this->assertSame('2026-06-02 15:30:00', $dto->updatedAt);
    }

    public function test_from_model_creates_dto_with_null_timestamps(): void
    {
        $favorite = new Favorite();
        $favorite->id = 202;
        $favorite->user_id = 15;
        $favorite->book_id = 88;
        $favorite->created_at = null;
        $favorite->updated_at = null;

        $dto = FavoriteResponseDto::fromModel($favorite);

        $this->assertSame(202, $dto->id);
        $this->assertSame(15, $dto->userId);
        $this->assertSame(88, $dto->bookId);
        $this->assertSame('', $dto->createdAt);
        $this->assertSame('', $dto->updatedAt);
    }
}
