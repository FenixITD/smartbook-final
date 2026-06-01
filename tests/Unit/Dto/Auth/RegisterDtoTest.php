<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Auth;

use App\Dto\Auth\RegisterDto;
use Tests\TestCase;

final class RegisterDtoTest extends TestCase
{
    public function test_register_dto_initializes_with_correct_properties(): void
    {
        $dto = new RegisterDto(
            'John Doe',
            'john@example.com',
            'strongpassword'
        );

        $this->assertSame('John Doe', $dto->name);
        $this->assertSame('john@example.com', $dto->email);
        $this->assertSame('strongpassword', $dto->password);
    }
}
