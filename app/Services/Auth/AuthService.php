<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Dto\Auth\LoginDto;
use App\Dto\Auth\RegisterDto;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

final readonly class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @param LoginDto $dto
     * @return bool
     *
     * Login with a request limit added to protect against password attacks.
     */
    public function login(LoginDto $dto): bool
    {
        $throttleKey = Str::transliterate(Str::lower($dto->email).'|'.$dto->ip);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        $attempt = Auth::attempt(
            credentials: [
                'email' => $dto->email,
                'password' => $dto->password,
            ],
            remember: $dto->remember,
        );

        if (!$attempt) {
            RateLimiter::hit($throttleKey);

            return false;
        }

        RateLimiter::clear($throttleKey);

        return true;
    }

    /**
     * @param RegisterDto $dto
     * @return void
     *
     * Registers a new user in the system and automatically authenticates them.
     */
    public function register(RegisterDto $dto): void
    {
        $user = $this->userRepository->create($dto);

        Auth::loginUsingId($user->id);
    }

    /**
     * @return void
     *
     * Logs out the currently authenticated user.
     */
    public function logout(): void
    {
        Auth::logout();
    }
}
