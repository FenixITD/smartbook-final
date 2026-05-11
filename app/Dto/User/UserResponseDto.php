<?php

declare(strict_types=1);

namespace App\Dto\User;

use App\Models\User;

final readonly class UserResponseDto
{
    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            role: $user->role,
            createdAt: $user->created_at?->toDateTimeString() ?? '',
            updatedAt: $user->updated_at?->toDateTimeString() ?? '',
        );
    }

    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }
}
