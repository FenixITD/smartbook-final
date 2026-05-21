<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Auth\RegisterDto;
use App\Dto\User\UserCredentialsDto;
use App\Dto\User\UserResponseDto;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

final class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): UserResponseDto|null
    {
        $user = User::where('email', $email)->first();

        return $user !== null ? UserResponseDto::fromModel($user) : null;
    }

    public function create(RegisterDto $dto): UserResponseDto
    {
        /** @var User $user */
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
        ]);

        /** @var User $fresh */
        $fresh = $user->fresh();

        return UserResponseDto::fromModel($fresh);
    }

    public function findCredentialsByEmail(string $email): UserCredentialsDto|null
    {
        $user = User::where('email', $email)
            ->select(['id', 'password'])
            ->first();

        return $user !== null
            ? new UserCredentialsDto($user->id, $user->password)
            : null;
    }

    public function createToken(int $userId, string $name): string
    {
        /** @var User $user */
        $user = User::findOrFail($userId);

        return $user->createToken($name)->plainTextToken;
    }
}
