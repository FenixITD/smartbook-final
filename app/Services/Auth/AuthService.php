<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Dto\Auth\LoginDto;
use App\Dto\Auth\RegisterDto;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function login(LoginDto $dto): bool
    {
        return Auth::attempt(
            credentials: [
                'email' => $dto->email,
                'password' => $dto->password,
            ],
            remember: $dto->remember,
        );
    }

    public function register(RegisterDto $dto): void
    {
        $user = $this->userRepository->create($dto);

        Auth::loginUsingId($user->id);
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
