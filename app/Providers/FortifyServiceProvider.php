<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
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
        Fortify::loginView('pages.auth.login');
        Fortify::registerView('pages.auth.register');
        Fortify::twoFactorChallengeView('pages.auth.two-factor-challenge');
        Fortify::confirmPasswordView('pages.auth.confirm-password');
        Fortify::requestPasswordResetLinkView('pages.auth.forgot-password');
        Fortify::resetPasswordView('pages.auth.reset-password');

        Fortify::createUsersUsing(CreateUser::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
    }
}
