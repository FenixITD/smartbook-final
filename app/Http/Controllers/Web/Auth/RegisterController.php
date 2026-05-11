<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class RegisterController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    public function show(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('pages.auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $this->authService->register($request->toDto());

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
