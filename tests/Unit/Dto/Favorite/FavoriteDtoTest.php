<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Favorite;

use App\Dto\Favorite\FavoriteDto;
use Tests\TestCase;

final class FavoriteDtoTest extends TestCase
{
    public function test_favorite_dto_initializes_and_returns_array(): void
    {
        $dto = new FavoriteDto(
            userId: 1,
            bookId: 42,
        );

        $this->assertSame(1, $dto->userId);
        $this->assertSame(42, $dto->bookId);

        $this->assertSame([
            'user_id' => 1,
            'book_id' => 42,
        ], $dto->toArray());
    }
}
