<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Auth;

use App\Dto\Auth\LoginDto;
use Tests\TestCase;

final class LoginDtoTest extends TestCase
{
    public function test_login_dto_initializes_with_correct_properties(): void
    {
        $dto = new LoginDto(
            'test@example.com',
            'secret123',
            true,
            '192.168.1.1'
        );

        $this->assertSame('test@example.com', $dto->email);
        $this->assertSame('secret123', $dto->password);
        $this->assertTrue($dto->remember);
        $this->assertSame('192.168.1.1', $dto->ip);
    }

    public function test_login_dto_accepts_null_ip(): void
    {
        $dto = new LoginDto(
            'admin@example.com',
            'password',
            false,
            null
        );

        $this->assertSame('admin@example.com', $dto->email);
        $this->assertSame('password', $dto->password);
        $this->assertFalse($dto->remember);
        $this->assertNull($dto->ip);
    }
}
