<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ApiLoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

final class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    public function __invoke(ApiLoginRequest $request): JsonResponse
    {
        $token = $this->authService->apiLogin($request->toDto());

        if ($token === null) {
            return response()->json(['message' => __('auth.failed')], 401);
        }

        return response()->json(['token' => $token]);
    }
}
