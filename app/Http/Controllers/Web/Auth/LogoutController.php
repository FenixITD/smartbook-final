<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LogoutRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;

final class LogoutController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    public function __invoke(LogoutRequest $request): RedirectResponse
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard');
    }
}
