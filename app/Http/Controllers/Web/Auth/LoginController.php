<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class LoginController extends Controller
{
    public function __construct(
        private readonly LoginService $authService,
    ) {
    }

    public function show(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('pages.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        if (!$this->authService->login($request->toDto())) {
            return back()
                ->withErrors(['email' => __('auth.failed')])
                ->onlyInput('email');
        }

        $user = Auth::user();

        if ($user !== null && $user->hasEnabledTwoFactorAuthentication()) {
            Auth::logout();

            $request->session()->put('login.id', $user->getKey());
            $request->session()->put('login.remember', $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->route('two-factor.login');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
