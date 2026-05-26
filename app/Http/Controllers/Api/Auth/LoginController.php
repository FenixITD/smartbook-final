<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Auth\LoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/auth/login',
    summary: 'Login user and get API token',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/LoginRequest'
        ),
    ),
    tags: ['Auth'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Successfully authenticated',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'token', type: 'string', example: '1|abc123xyz'),
                ],
                type: 'object',
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Invalid credentials',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'These credentials do not match our records.'),
                ],
                type: 'object',
            )
        ),
    ]
)]
final class LoginController extends Controller
{
    public function __construct(
        private readonly LoginService $authService,
        private readonly UserRepositoryInterface $repository,
    ) {
    }

    public function __invoke(LoginRequest $request): JsonResponse
    {
        if (!$this->authService->login($request->toDto())) {
            return response()->json(['message' => __('auth.failed')], 401);
        }

        $token = $this->repository->createToken((int) Auth::id(), 'api-token');

        return response()->json(['token' => $token]);
    }
}
