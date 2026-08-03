<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::requestPasswordResetLinkView('pages.auth.forgot-password');
        Fortify::resetPasswordView('pages.auth.reset-password');
        Fortify::twoFactorChallengeView('pages.auth.two-factor-challenge');
        Fortify::confirmPasswordView('pages.auth.confirm-password');
        Fortify::verifyEmailView('pages.auth.verify-email');

        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
    }
}
