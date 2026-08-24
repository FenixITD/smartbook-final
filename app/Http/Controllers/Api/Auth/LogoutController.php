<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/auth/logout',
    summary: 'Logout user and revoke current API token',
    security: [['bearerAuth' => []]],
    tags: ['Auth'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Successfully logged out',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully'),
                ],
                type: 'object',
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: []
        ),
    ]
)]
final readonly class LogoutController
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
