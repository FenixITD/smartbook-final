<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Author;

use App\Dto\Author\AuthorResponseDto;
use App\Models\Author;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class AuthorResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data(): void
    {
        $author = new Author();
        $author->id = 1;
        $author->name = 'J.R.R. Tolkien';
        $author->created_at = Carbon::parse('2026-06-01 10:00:00');
        $author->updated_at = Carbon::parse('2026-06-02 10:00:00');
        $author->books_count = 5;

        $dto = AuthorResponseDto::fromModel($author);

        $this->assertSame(1, $dto->id);
        $this->assertSame('J.R.R. Tolkien', $dto->name);
        $this->assertSame('2026-06-01 10:00:00', $dto->createdAt);
        $this->assertSame('2026-06-02 10:00:00', $dto->updatedAt);
        $this->assertSame(5, $dto->booksCount);
    }

    public function test_from_model_creates_dto_with_null_dates_and_missing_count(): void
    {
        $author = new Author();
        $author->id = 2;
        $author->name = 'George R.R. Martin';
        $author->created_at = null;
        $author->updated_at = null;

        $dto = AuthorResponseDto::fromModel($author);

        $this->assertSame(2, $dto->id);
        $this->assertSame('George R.R. Martin', $dto->name);
        $this->assertSame('', $dto->createdAt);
        $this->assertSame('', $dto->updatedAt);
        $this->assertSame(0, $dto->booksCount);
    }
}
