<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Genre;

use App\Dto\Genre\GenreResponseDto;
use App\Models\Genre;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class GenreResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data(): void
    {
        $genre = new Genre();
        $genre->id = 7;
        $genre->name = 'Horror';
        $genre->slug = 'horror';
        $genre->created_at = Carbon::parse('2026-03-15 09:00:00');
        $genre->updated_at = Carbon::parse('2026-03-16 10:30:00');
        $genre->books_count = 42;

        $dto = GenreResponseDto::fromModel($genre);

        $this->assertSame(7, $dto->id);
        $this->assertSame('Horror', $dto->name);
        $this->assertSame('horror', $dto->slug);
        $this->assertSame('2026-03-15 09:00:00', $dto->createdAt);
        $this->assertSame('2026-03-16 10:30:00', $dto->updatedAt);
        $this->assertSame(42, $dto->booksCount);
    }

    public function test_from_model_creates_dto_with_null_fields_and_missing_count(): void
    {
        $genre = new Genre();
        $genre->id = 12;
        $genre->name = 'Biography';
        $genre->slug = 'biography';
        $genre->created_at = null;
        $genre->updated_at = null;

        $dto = GenreResponseDto::fromModel($genre);

        $this->assertSame(12, $dto->id);
        $this->assertSame('', $dto->createdAt);
        $this->assertSame('', $dto->updatedAt);
        $this->assertSame(0, $dto->booksCount);
    }
}
