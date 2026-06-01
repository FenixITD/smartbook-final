<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Author;

use App\Dto\Author\AuthorDto;
use Tests\TestCase;

final class AuthorDtoTest extends TestCase
{
    public function test_author_dto_initializes_and_returns_array(): void
    {
        $dto = new AuthorDto('Stephen King');

        $this->assertSame('Stephen King', $dto->name);
        $this->assertSame(['name' => 'Stephen King'], $dto->toArray());
    }
}
