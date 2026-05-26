<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class RegisterController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
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
        $user = $this->repository->create($request->toDto());
        Auth::loginUsingId($user->id);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
