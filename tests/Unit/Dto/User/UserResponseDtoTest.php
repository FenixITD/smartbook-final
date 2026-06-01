<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\User;

use App\Dto\User\UserResponseDto;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class UserResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data(): void
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'John Doe';
        $user->email = 'john@example.com';
        $user->role = 'customer';
        $user->created_at = Carbon::parse('2026-06-01 12:00:00');
        $user->updated_at = Carbon::parse('2026-06-01 13:00:00');

        $dto = UserResponseDto::fromModel($user);

        $this->assertSame(1, $dto->id);
        $this->assertSame('John Doe', $dto->name);
        $this->assertSame('john@example.com', $dto->email);
        $this->assertSame('customer', $dto->role);
        $this->assertSame('2026-06-01 12:00:00', $dto->createdAt);
        $this->assertSame('2026-06-01 13:00:00', $dto->updatedAt);
    }

    public function test_from_model_handles_null_timestamps(): void
    {
        $user = new User();
        $user->id = 2;
        $user->name = 'Admin';
        $user->email = 'admin@example.com';
        $user->role = 'admin';
        $user->created_at = null;
        $user->updated_at = null;

        $dto = UserResponseDto::fromModel($user);

        $this->assertSame(2, $dto->id);
        $this->assertSame('', $dto->createdAt);
        $this->assertSame('', $dto->updatedAt);
    }
}
