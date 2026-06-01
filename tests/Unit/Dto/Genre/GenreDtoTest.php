<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Genre;

use App\Dto\Genre\GenreDto;
use Tests\TestCase;

final class GenreDtoTest extends TestCase
{
    public function test_genre_dto_initializes_and_returns_array(): void
    {
        $dto = new GenreDto(
            'Sci-Fi',
            'sci-fi',
        );

        $this->assertSame('Sci-Fi', $dto->name);
        $this->assertSame('sci-fi', $dto->slug);

        $this->assertSame([
            'name' => 'Sci-Fi',
            'slug' => 'sci-fi',
        ], $dto->toArray());
    }
}
