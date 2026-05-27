<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Dto\Auth\LoginDto;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class LoginService
{
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
}
