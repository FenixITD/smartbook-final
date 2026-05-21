<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Auth\RegisterDto;
use App\Dto\User\UserCredentialsDto;
use App\Dto\User\UserResponseDto;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): UserResponseDto|null;

    public function create(RegisterDto $dto): UserResponseDto;

    public function findCredentialsByEmail(string $email): UserCredentialsDto|null;

    public function createToken(int $userId, string $name): string;
}
