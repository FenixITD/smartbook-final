<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Dto\Auth\LoginDto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginService
{
    private const MAX_ATTEMPTS_PER_CREDENTIAL = 5;
    private const MAX_ATTEMPTS_PER_IP = 20;

    public function login(LoginDto $dto): bool
    {
        $throttleKey = Str::transliterate(Str::lower($dto->email).'|'.$dto->ip);
        $ipThrottleKey = 'login-ip:'.Str::transliterate($dto->ip);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS_PER_CREDENTIAL)) {
            throw $this->throttled($throttleKey);
        }

        if (RateLimiter::tooManyAttempts($ipThrottleKey, self::MAX_ATTEMPTS_PER_IP)) {
            throw $this->throttled($ipThrottleKey);
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
            RateLimiter::hit($ipThrottleKey);

            return false;
        }

        RateLimiter::clear($throttleKey);

        return true;
    }

    private function throttled(string $throttleKey): ValidationException
    {
        $seconds = RateLimiter::availableIn($throttleKey);

        return ValidationException::withMessages(['email' => __('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)])]);
    }
}
