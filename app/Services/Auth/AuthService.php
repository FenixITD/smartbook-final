<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Dto\Auth\ApiLoginDto;
use App\Dto\Auth\LoginDto;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AuthService
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
     * @param ApiLoginDto $dto
     * @return string|null
     *
     * Api gogin with a request limit added to protect against password attacks.
     */
    public function apiLogin(ApiLoginDto $dto): string|null
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

        $credentials = $this->userRepository->findCredentialsByEmail($dto->email);

        if ($credentials === null || ! Hash::check($dto->password, $credentials->password)) {
            RateLimiter::hit($throttleKey);

            return null;
        }

        RateLimiter::clear($throttleKey);

        return $this->userRepository->createToken($credentials->id, 'api-token');
    }
}
